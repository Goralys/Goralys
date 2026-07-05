<?php

/*
 *  ______   ______   ______   ______   __       __  __   ______
 * /\  ___\ /\  __ \ /\  == \ /\  __ \ /\ \     /\ \_\ \ /\  ___\
 * \ \ \__ \\ \ \/\ \\ \  __< \ \  __ \\ \ \____\ \____ \\ \___  \
 *  \ \_____\\ \_____\\ \_\ \_\\ \_\ \_\\ \_____\\/\_____\\/\_____\
 *   \/_____/ \/_____/ \/_/ /_/ \/_/\/_/ \/_____/ \/_____/ \/_____/

 *                         Goralys 3.1.0
 */

/*
 * Goralys — application de gestion des sujets du Grand oral
    Copyright (C) 2025-2026 Sami Saubion

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Goralys\Kernel;

use ErrorException;
use Goralys\App\Config\RateLimiterConfig;
use Goralys\App\Context\AppContext;
use Goralys\App\Context\Data\ToastMode;
use Goralys\App\HTTP\Files\GoralysFileManager;
use Goralys\App\HTTP\Files\Interface\FileExtractor;
use Goralys\App\HTTP\Files\Interface\FileMover;
use Goralys\App\HTTP\Files\Services\HttpFileExtractor;
use Goralys\App\HTTP\Files\Services\HttpFileMover;
use Goralys\App\HTTP\Files\Services\HttpFileResponder;
use Goralys\App\HTTP\Guard\HttpGuard;
use Goralys\App\HTTP\Guard\Interface\GuardInterface;
use Goralys\App\HTTP\JSON\Services\HttpJsonResponder;
use Goralys\App\HTTP\Request\GoralysRequest;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\HTTP\Response\DeferredResponse;
use Goralys\App\HTTP\Response\ImmediateResponse;
use Goralys\App\HTTP\Response\Interfaces\DeferredResponseInterface;
use Goralys\App\HTTP\Response\Interfaces\ImmediateResponseInterface;
use Goralys\App\RateLimiter\RateLimiter;
use Goralys\App\Security\CSRF\Services\CSRFService;
use Goralys\App\Subjects\Controllers\SubjectsController;
use Goralys\App\Support\Controllers\SupportController;
use Goralys\App\Topics\Controllers\TopicsController;
use Goralys\App\User\Controllers\AuthController;
use Goralys\App\User\Controllers\UserController;
use Goralys\App\User\Data\Enums\UserAuthStatus;
use Goralys\App\User\Data\UsernameTable;
use Goralys\App\Utils\Toast\Controllers\ToastController;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Repository\UserRepository;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Kernel\Data\ErrorMessageConfig;
use Goralys\Platform\DB\Facade\DbContainer;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Doc\PDF\DomPdfExporter;
use Goralys\Platform\Loader\Services\EnvService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\GoralysLogger;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Mail\Facade\MailContainer;
use Goralys\Platform\Mail\Interfaces\MailContainerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\DB\GoralysConnectException;
use Goralys\Shared\Exception\GoralysException;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\Lib\GoralysLib;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

/**
 * The kernel used by the API to access the database, environment, and toast controller.
 */
class GoralysKernel
{
    public EnvService $env;
    public GoralysLib $utils;
    public DbContainerInterface $db;
    public MailContainerInterface $mailer;
    public LoggerInterface $logger;
    public AuthController $auth;
    public UserController $users;
    public GoralysFileManager $fileManager;
    public SubjectsController $subjects;
    public TopicsController $topics;
    public ToastController $toast;
    public SupportController $support;
    public GuardInterface $guard;
    public CSRFService $csrf;
    public UsernameManager $usernames;
    private string $rootPath;
    private RateLimiter $rateLimiter;
    /**
     * This variable is used to determine the context of the app.
     * @var AppContext
     */
    private AppContext $context;
    private RequestInterface $request;
    private DomPdfExporter $exporter;
    private int $sessionLifetime;
    /**
     * Multiplier applied to the base session lifetime to determine the upper bound
     * after which a user is no longer considered authenticated.
     *
     * This multiplier introduces an intermediate authentication state used for
     * user-facing feedback:
     *
     * - If the time since the last activity is less than the base session lifetime,
     *   the user is considered fully authenticated.
     * - If it exceeds the base session lifetime but remains below
     *   (session lifetime * multiplier), the session is considered expired,
     *   but the user is still distinguishable from a fully unauthenticated state.
     * - If it exceeds (session lifetime * multiplier), the user is considered
     *   not authenticated.
     *
     * In both non-authenticated and expired states, the underlying session is unset
     * and destroyed; the distinction exists solely to provide more accurate user
     * feedback and state reporting.
     *
     * The multiplier value is configured via the environment configuration (.env).
     *
     * @var float
     */
    private readonly float $sessionLifetimeMultiplier;
    private int $sinceLastActivity;

    /* @var array<class-string<Throwable>, ErrorMessageConfig> */
    private array $errorMessages = [];

    /**
     * Initializes the kernel and all of its members.
     * @param string $rootPath The path to the .env file and that is considered to be the root path for the kernel.
     * @param FileMover|null $mover The file mover used by the kernel.
     */
    public function __construct(string $rootPath, ?FileMover $mover = null)
    {
        $this->rootPath = $rootPath;

        $this->initEnv();
        $this->initUtils();
        $this->initLogger();
        $this->sessionLifetime = $this->env->getByKey("PHP_SESSION_LIFETIME");
        $this->sessionLifetimeMultiplier = $this->env->getByKey("PHP_SESSION_LIFETIME_MULTIPLIER");
        $this->startSession();

        // Initializes toast before the DB to be able to provide user feedback if the connection to the DB fails.
        $this->initToast();
        $this->context = new AppContext(ToastMode::DEFAULT, trim($this->env->getByKey("ORIGIN_DOMAIN")));

        $this->initDb();
        $this->initMailer();
        $this->initAuth();
        $this->initUser();
        $this->initUsernameManager();
        $this->initSupport();
        $this->bootFileSubsystem($mover);
        $this->initExporter();
        $this->initSubjects();
        $this->initTopics();
        $this->initCSRF();
        $this->initGuard();
        $this->initRateLimiter();
    }

    /**
     * Loads the environment variables inside $_ENV using `DotEnv`.
     * The path to the .env file is supposed to be `$rootPath`.
     * @return void
     */
    private function initEnv(): void
    {
        $this->env = new EnvService();
        $this->env->load($this->rootPath);
    }

    /**
     * Initializes all the utility services
     * @return void
     */
    private function initUtils(): void
    {
        $this->utils = new GoralysLib();
    }

    /**
     * Initializes the logger of the kernel.
     * The logger used is a `GoralysLogger`, which is a custom logger made specially for this project.
     * @return void
     */
    private function initLogger(): void
    {
        $this->logger = new GoralysLogger();
        $this->logger->rotate();
    }

    /**
     * Starts the PHP session if it is not already started.
     * @return void
     */
    private function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.gc_maxlifetime', $this->sessionLifetime);
            ini_set('session.cookie_lifetime', $this->sessionLifetime);

            session_set_cookie_params([
                // Ensure the session expiration logic works as intended. Refer to variable docs for more info.
                'lifetime' => $this->sessionLifetime * $this->sessionLifetimeMultiplier,
                'path' => '/',
                'domain' => $this->env->getByKey("COOKIES_DOMAIN"),
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_name("GORALYSSESSID");

            session_start();

            $this->sinceLastActivity = isset($_SESSION['LAST_ACTIVITY'])
                ? time() - $_SESSION['LAST_ACTIVITY']
                : -1;

            $_SESSION['LAST_ACTIVITY'] = time();
        }
    }

    /**
     * Initializes the toast controller used by the kernel.
     * @return void
     */
    private function initToast(): void
    {
        $this->toast = new ToastController();
    }

    /**
     * Initializes the database container of the kernel.
     * Note that the database connection won't be established until the `connect` method of the kernel is called.
     * @return void
     */
    private function initDb(): void
    {
        $this->db = new DbContainer($this->logger);
    }

    /**
     * Initializes the mail container of the kernel.
     * @return void
     */
    private function initMailer(): void
    {
        $this->mailer = new MailContainer($this->env, $this->logger);
    }

    /**
     * Initializes the authentification controller of the kernel.
     * @return void
     */
    private function initAuth(): void
    {
        $this->auth = new AuthController(
            $this->logger,
            $this->db,
            $this->sessionLifetime,
            $this->sessionLifetimeMultiplier,
        );
    }

    /**
     * Initializes the user controller of the kernel.
     * @return void
     */
    private function initUser(): void
    {
        $this->users = new UserController(
            $this->logger,
            $this->db,
        );
    }

    /**
     * Initializes the kernel's username manager.
     * @return void
     */
    private function initUsernameManager(): void
    {
        $this->usernames = new UsernameManager(new UserRepository($this->logger, $this->db));
    }

    /**
     * Initializes the support controller of the kernel.
     * @return void
     */
    private function initSupport(): void
    {
        $this->support = new SupportController(
            $this->logger,
            $this->db,
            $this->usernames,
        );
    }

    /**
     * Initializes the files-related subservices for the kernel.
     * @param FileMover|null $mover The file mover for the kernel.
     * @return void
     */
    private function bootFileSubsystem(?FileMover $mover): void
    {
        $resolvedMover = $mover ?? new HttpFileMover();

        $extractor = new HttpFileExtractor();

        try {
            $this->initFileManager($resolvedMover, $extractor, $resolvedMover->getFiles());
        } catch (GoralysRuntimeException $e) {
            $this->logger->fatal(
                LoggerInitiator::KERNEL,
                "A GoralysRuntimeException occurred while initializing the kernel: " . $e->getMessage(),
            );
        }
    }

    /**
     * Initializes the file manager of the kernel.
     * @param FileMover $mover The mover for the file manager.
     * @param FileExtractor $extractor The file extractor for the file manager.
     * @param array $files The files array, used only in "test mode".
     * @return void
     * @throws GoralysRuntimeException If an invalid file is found.
     */
    private function initFileManager(FileMover $mover, FileExtractor $extractor, array $files): void
    {
        $this->fileManager = new GoralysFileManager($files, $mover, $extractor, $this->logger);
    }

    /**
     * Initializes the PDF exporter of the kernel.
     * @return void
     */
    private function initExporter(): void
    {
        $this->exporter = new DomPdfExporter($this->logger);
    }

    /**
     * Initializes the subject controller of the kernel.
     * @return void
     */
    private function initSubjects(): void
    {
        $this->subjects = new SubjectsController($this->logger, $this->db, $this->fileManager, $this->exporter);
    }

    /**
     * Initializes the topic controller of the kernel.
     * @return void
     */
    private function initTopics(): void
    {
        $this->topics = new TopicsController(
            $this->db,
            new UsernameTable(),
            $this->fileManager,
            $this->logger
        );
    }

    /**
     * Initializes the CSRF service used by the kernel
     * @return void
     */
    private function initCSRF(): void
    {
        $this->csrf = new CSRFService($this->logger);
    }

    /**
     * Initializes the guard used by the kernel.
     * @return void
     */
    private function initGuard(): void
    {
        $this->guard = new HttpGuard($this->usernames, $this->context);
    }

    /**
     * Initializes the kernel's rate limiter.
     * @return void
     */
    private function initRateLimiter(): void
    {
        $this->rateLimiter = new RateLimiter($this->logger);
        if (strtolower($this->env->getByKey("GORALYS_ENVIRONMENT")) === "dev") {
            $this->rateLimiter->disable();
        }
    }

    /**
     * Sets the exceptions and errors handlers to be the ones from the `GoralysKernel`.
     * @return void
     */
    public function setHandlers(): void
    {
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
    }

    /**
     * Sets a custom message for the given exception.
     * This message will then be sent to the user if the exception is thrown.
     * @param class-string<Throwable> $eClass The class of the targeted exception.
     * @param string $msg The message to display.
     * @param int $code The HTTP response code to send along with the message.
     * @param string $redirect The page to redirect the user to.
     * @return void
     */
    public function setExceptionMessage(string $eClass, string $msg, int $code = 500, string $redirect = "/"): void
    {
        $this->errorMessages[$eClass] = new ErrorMessageConfig($msg, $redirect, $code);
    }

    /**
     * The custom error handler for the kernel.
     * It ignores errors considered "non-fatal":
     * - `E_ERROR`
     * - `E_USER_ERROR`
     * - `E_CORE_ERROR`
     * - `E_COMPILE_ERROR`
     * - `E_RECOVERABLE_ERROR`
     * @param int $severity The severity of the error.
     * @param string $message The error's message.
     * @param string $file The file where the error occurred.
     * @param int $line The line that caused the error.
     * @return void
     * @throws ErrorException Only thrown when the exception is considered fatal.
     */
    public function errorHandler(int $severity, string $message, string $file, int $line): void
    {
        $this->logger->warning(
            LoggerInitiator::APP,
            "PHP Error (severity $severity) : $message — $file:$line",
        );

        // Ignore non-fatal errors
        if (!in_array($severity, [E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Runs the provided function and catches any exception to handle it.
     * @param callable $callback The function to execute.
     * @return void
     */
    public function run(callable $callback): void
    {
        try {
            $callback($this, $this->request);

            if (session_status() == PHP_SESSION_ACTIVE) {
                session_write_close();
            }
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * The custom exception handler for the kernel.
     * It handles `GoralysException` and its instances as "normal" errors.
     * Thus for other exceptions, it toasts them as unexpected.
     * @param Throwable $e The thrown exception.
     * @return void
     */
    #[NoReturn]
    public function exceptionHandler(Throwable $e): void
    {
        $trace = $e->getTrace();
        $traceLines = array_slice($trace, 0, 5);

        $callStack = "\n\t" . $e->getFile() . ":" . $e->getLine();
        foreach ($traceLines as $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? '?';
            $callStack .= "\n\t" . $file . ":" . $line;
        }

        $this->logger->error(
            LoggerInitiator::APP,
            "Uncaught exception: " . $e->getMessage() . $callStack,
        );

        if (isset($this->errorMessages[$e::class])) {
            $msg = $this->errorMessages[$e::class];
            $this->deferredResponse($msg->code)->error(
                $msg->message,
            )
                ->redirect($msg->redirect)
                ->send();
        } elseif ($e instanceof GoralysException) {
            $this->deferredResponse(500)->error( // Internal Server Error
                "Une erreur interne est survenue",
            )
                ->redirect("/")
                ->send();
        } else {
            $this->deferredResponse(500)->error( // Internal Server Error
                "Une erreur inattendue s'est produite",
            )
                ->redirect("/")
                ->send();
        }
    }

    public function deferredResponse(int $code = 200): DeferredResponseInterface
    {
        return new DeferredResponse($this->context, $code);
    }

    /**
     * Helper used to centralize db connection logic and failure behavior.
     * @return void
     */
    public function requireDb(): void
    {
        try {
            if (!$this->connect()) {
                $this->deferredResponse(500)->error( // Internal server error
                    "Une erreur interne est survenue lors de la connexion, veuillez réessayer ultérieurement.",
                )
                    ->redirect("/")
                    ->send();
            }
        } catch (Throwable) {
            $this->deferredResponse(500)->error( // Internal server error
                "Une erreur interne est survenue lors de la connexion, veuillez réessayer ultérieurement.",
            )
                ->redirect("/")
                ->send();
        }
    }

    /**
     * Connects the kernel to the database.
     * @throws GoralysConnectException Throws an exception if the connection with the database could not be established.
     */
    private function connect(): bool
    {
        if (!isset($this->db)) {
            $this->db = new DbContainer($this->logger);
        }
        return $this->db->connect();
    }

    /**
     * Helper to require a rate limit for a given endpoint/action.
     * @param string $endpoint The endpoint to require rate limiting on
     * (refer to {@see RateLimiterConfig} for endpoint-specific rates).
     * @param string $redirect The url to redirect the user to on failure.
     * @param string $message The message to display on failure (vie toast notification).
     * @return void
     */
    public function requireRateLimit(
        string $endpoint,
        string $redirect = "/",
        string $message = "Vous avez atteint la limite périodique de requêtes. Veuillez réessayer ultérieurement.",
    ): void {
        if (!$this->rateLimiter->forwardRequest($endpoint)) {
            $this->deferredResponse(429)->toast(ToastType::WARNING, "Limite atteinte", $message)
                ->redirect($redirect)
                ->send();
        }
    }

    /**
     * Helper to check if the user is authenticated
     * @param string $endpoint The endpoint the authentification is required in.
     * @return void
     */
    public function requireAuth(string $endpoint): void
    {
        $this->logger->debug(LoggerInitiator::KERNEL, "Session lifetime : " . $this->sessionLifetime);
        $this->logger->debug(LoggerInitiator::KERNEL, "Since last activity : " . $this->sinceLastActivity);

        switch ($this->auth->getAuthStatus($this->sinceLastActivity)) {
            case UserAuthStatus::SESSION_EXPIRED:
                $this->destroySession();
                $this->logger->warning(
                    LoggerInitiator::CORE,
                    "Tried to perform action: $endpoint without authentification",
                );

                $this->response(401)->json(["authEvent" => "expired"]); // Unauthorized
                // no break
            case UserAuthStatus::NOT_AUTHENTICATED:
                $this->destroySession();
                $this->logger->warning(
                    LoggerInitiator::CORE,
                    "Tried to perform action: $endpoint without authentification",
                );

                $this->response(401)->json(["authEvent" => "unauthenticated"]); // Unauthorized
                // no break
            case UserAuthStatus::AUTHENTICATED:
                break;
        }
    }

    private function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Generate a new HTTP response.
     * @param int $code The response's code (default = 200).
     * @return ImmediateResponseInterface The response.
     */
    public function response(int $code = 200): ImmediateResponseInterface
    {
        $files = new HttpFileResponder();
        $json = new HttpJsonResponder();
        return new ImmediateResponse($code, $this->logger, $files, $json);
    }

    /**
     * Helper to check if the user is authenticated
     * @return bool If the user is authenticated
     */
    public function checkAuth(): bool
    {
        return $this->auth->getAuthStatus($this->sinceLastActivity) == UserAuthStatus::AUTHENTICATED;
    }

    /**
     * Helper to use CSRF in an API endpoint.
     * It should always be called after you already called getRequest on the kernel.
     * @param string $formId The id of the current form.
     * @param ?string $redirect The page to redirect the user to.
     * @return void
     */
    public function requireCSRF(string $formId, ?string $redirect = null): void
    {

        if (!$this->csrf->validate($formId, $this->request())) {
            $this->deferredResponse(403)->toast( // Forbidden
                ToastType::WARNING,
                "Lien externe",
                "Ce lien semble inconnu. Ne faite pas confiance aux sources externes.",
            )
                ->redirect($redirect ?? "/")
                ->send();
        }
    }

    /**
     * Gets the kernel's current HTTP request
     * @return RequestInterface The request.
     */
    public function request(): RequestInterface
    {
        if (!isset($this->request)) {
            $this->request = new GoralysRequest();
        }

        return $this->request;
    }

    /**
     * Helper to check if the user has a certain role.
     * @param UserRole $role The minimum role the user must have.
     * @param bool $strict If set to true, the user must be exactly the provided role.
     * @return void
     */
    public function requireRole(UserRole $role, bool $strict = false): void
    {
        if (!isset($_SESSION[GoralysConfig::SESSION::ROLE])) {
            $this->destroySession();

            $this->response(401)->json(["authEvent" => "expired"]); // Unauthorized
        }

        $currentRole = UserRole::fromString($_SESSION[GoralysConfig::SESSION::ROLE]);
        $pass = !($strict ? $currentRole !== $role : !$currentRole->isAtLeast($role));

        if (!$pass) {
            $this->logger->warning(
                LoggerInitiator::KERNEL,
                "Tried to perform forbidden action with role " . $currentRole->toString(
                ) . "(required " . ($strict ? "==" : "=<") . " " . $role->toString() . ")"
            );
            $this->deferredResponse(403)->error( // Forbidden
                "Il semblerait que vous n'ayez pas les permissions nécéssaires . ",
            )
                    ->redirect("/user/login")
                    ->send();
        }
    }

    /**
     * Switch the {@see AppContext::mode} to flash toast.
     * @return void
     */
    public function useFlash(): void
    {
        $this->context->mode = ToastMode::FLASH;
    }
}
