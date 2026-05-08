<?php

use Goralys\App\Config\AppConfig;
use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\DbMiddleware;
use Goralys\App\HTTP\Middleware\MiddlewareSets;
use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Middleware\RoleMiddleware;
use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Subjects\Data\Enums\SubjectFields;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Kernel\GoralysKernel;

function createSubjectsRoutes(GoralysRouter $router): void
{
    // ================================================
    // [SECTION] Subjects getters
    // ================================================
    $router->post('subjects/admin', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::ADMIN);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-admin-subjects', UserRole::ADMIN));

    $router->post('subjects/teacher', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::TEACHER);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-teacher-subjects', UserRole::TEACHER));

    $router->post('subjects/student', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::STUDENT);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-student-subjects', UserRole::STUDENT));

    // -------------------------
    // [SUB SECTION] Get draft
    // -------------------------
    $router->post(
        'subjects/draft',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $path = $kernel->subjects->draftsManager->getPath(
                $kernel->usernameManager->get($request->get("student-token")),
                $kernel->usernameManager->get($request->get("teacher-token")),
                $request->get("topic"),
            );

            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $kernel->response()->download($path, $request->get("file-name") . "." . $extension);
        },
        ...RouterOptions::$INPUT::require("teacher-token", "student-token", "topic", "file-name"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur est survenue lors de la récupération du brouillon de l'élève, 
                veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
            ->middleware(...RateLimitMiddleware::for('get-student-draft', '/subject/'))
            ->middleware(...AuthMiddleware::require())
            ->middleware(...RoleMiddleware::require(UserRole::TEACHER, true))
            ->middleware(...DbMiddleware::require())
            ->middleware(...ToastMiddleware::flash());

    // ================================================
    // [SECTION] Subject modifiers/setters
    // ================================================
    $router->post('subjects/export', function (GoralysKernel $kernel) {
        $kernel->subjects->cleanExports(); // Cleans all previous exports

        $subjects = $kernel->subjects->getForRole(UserRole::ADMIN);
        $path = $kernel->subjects->exportAll($subjects);

        $kernel->response()->download($path, "sujets-go.zip", after: fn() => $kernel->subjects->cleanExports());
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('export-subjects', UserRole::ADMIN));

    // -------------------------
    // [SUB SECTION] Student
    // -------------------------
    $router->post(
        'subjects/save-draft',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'student-token')?->send();

            $result = $kernel->subjects->updateField(
                $kernel->usernameManager->get($request->get('teacher-token')),
                $kernel->usernameManager->get($request->get('student-token')),
                $request->get('topic'),
                SubjectFields::SUBJECT,
                $request->get('draft'),
                (bool) $request->get('interdisciplinary'),
            );

            if (!$result) {
                $kernel->deferredResponse(500)->error( // Internal server error
                    "Une erreur interne est survenue lors de l'enregistrement de votre brouillon, 
            veuillez réessayer ultérieurement.",
                )
                ->send();
            }

            $kernel->deferredResponse()->toast(
                ToastType::INFO,
                "Question",
                "Votre brouillon a bien été enregistré.",
            )
            ->send();
        },
        ...RouterOptions::$INPUT::require("draft", "topic", "teacher-token", "student-token"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de l'enregistrement de votre brouillon, 
            veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
            ->middlewares(...MiddlewareSets::subjectsRoute('save-draft', UserRole::STUDENT));

    $router->post(
        'subjects/submit',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'student-token')?->send();

            $teacherUsername = $kernel->usernameManager->get($request->get('teacher-token'));
            $studentUsername = $kernel->usernameManager->get($request->get('student-token'));
            $topic = $request->get('topic');
            $subject = $request->get('subject');
            $interdisciplinary = (bool) $request->get('interdisciplinary');
            $draftFile = $kernel->fileManager->get("draft-file");

            if ($draftFile && $draftFile->size > AppConfig::MAX_DRAFT_SIZE) {
                $kernel->deferredResponse()->toast(
                    ToastType::WARNING,
                    "Fichier",
                    "Ce fichier dépasse la taille maximale de 50 KO, veuillez ressayez avec un fichier plus petit.",
                )->send();
            }

            $subjectResult = $kernel->subjects->updateField(
                $teacherUsername,
                $studentUsername,
                $topic,
                SubjectFields::SUBJECT,
                $subject,
                $interdisciplinary,
            );

            if (!$subjectResult) {
                $kernel->db->rollback();
                $kernel->toast->fatalError(
                    500,
                    "Une erreur interne est survenue lors de l'enregistrement de votre question, 
            veuillez réessayer ultérieurement.",
                );
            }

            $statusResult = $kernel->subjects->updateField(
                $teacherUsername,
                $studentUsername,
                $topic,
                SubjectFields::STATUS,
                SubjectStatus::SUBMITTED,
            );

            if (!$statusResult) {
                $kernel->db->rollback();
                $kernel->toast->fatalError(
                    409,
                    "Votre question n'a pas pu être envoyée. Veuillez réessayer ultérieurement.",
                );
            }

            if ($draftFile) {
                $updateResult = $kernel->subjects->draftsManager->update($studentUsername, $teacherUsername, $topic);

                if (!$updateResult) {
                    $kernel->db->rollback();
                    $kernel->toast->fatalError(
                        500,
                        "Votre question n'a pas pu être envoyée, car votre brouillon n'a pas pu être enregistré. 
                Veuillez réessayer ultérieurement.",
                    );
                }
            }

            $kernel->db->commit();
            $kernel->toast->showToast(ToastType::INFO, "Question", "Votre question a bien été envoyée.", "");
            $kernel->response()->http();
        },
        ...RouterOptions::$INPUT::require("subject", "topic", "teacher-token", "student-token"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de l'envoi de votre question, veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
            ->middlewares(...MiddlewareSets::subjectsRoute('submit-subject', UserRole::STUDENT));

    // -------------------------
    // [SUB SECTION] Teacher
    // -------------------------
    $router->post(
        'subjects/reject',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'teacher-token')?->send();

            $teacherUsername = $kernel->usernameManager->get($request->get('teacher-token'));
            $studentUsername = $kernel->usernameManager->get($request->get('student-token'));
            $topic = $request->get('topic');
            $comment = $request->get('comment');
            $currentStatus = $kernel->subjects->getStatus($teacherUsername, $studentUsername, $topic);

            if ($currentStatus === SubjectStatus::REJECTED) {
                $kernel->deferredResponse()->toast(
                    ToastType::INFO,
                    "Invalidation",
                    "Cette question est déjà invalidée.",
                )->send();
            }

            if ($currentStatus !== SubjectStatus::SUBMITTED) {
                $kernel->deferredResponse(409)->error("Vous ne pouvez pas rejeter cette question.")->send();
            }

            $commentResult = $kernel->subjects->updateField(
                $teacherUsername,
                $studentUsername,
                $topic,
                SubjectFields::COMMENT,
                $comment,
            );
            if (!$commentResult) {
                $kernel->db->rollback();
                $kernel->deferredResponse(500)->error("Impossible d'enregistrer votre commentaire.")->send();
            }

            $statusResult = $kernel->subjects->updateField(
                $teacherUsername,
                $studentUsername,
                $topic,
                SubjectFields::STATUS,
                SubjectStatus::REJECTED,
            );
            if (!$statusResult) {
                $kernel->db->rollback();
                $kernel->deferredResponse(500)->error("La question n'a pas pu être invalidée.")->send();
            }

            $kernel->db->commit();
            $kernel->deferredResponse()->toast(
                ToastType::INFO,
                "Invalidation",
                "La question a bien été invalidée.",
            )->send();
        },
        ...RouterOptions::$INPUT::require("comment", "topic", "teacher-token", "student-token"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de l'invalidation de la question, 
                veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
            ->middlewares(...MiddlewareSets::subjectsRoute('reject-subject', UserRole::TEACHER, transaction: true));

    $router->post(
        'subjects/approve',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'teacher-token')?->send();

            $result = $kernel->subjects->updateField(
                $kernel->usernameManager->get($request->get('teacher-token')),
                $kernel->usernameManager->get($request->get('student-token')),
                $request->get("topic"),
                SubjectFields::STATUS,
                SubjectStatus::APPROVED,
            );

            if (!$result) {
                $kernel->deferredResponse(500)->error( // Internal server error
                    "Une erreur interne est survenue lors de la validation de la question, 
                veuillez réessayer ultérieurement.",
                )
                ->send();
            }

            $kernel->deferredResponse()->toast(
                ToastType::INFO,
                "Validation",
                "La question a bien été validée.",
            )
            ->send();
        },
        ...RouterOptions::$INPUT::require("topic", "teacher-token", "student-token"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de la validation de la question, 
                veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
    ->middlewares(...MiddlewareSets::subjectsRoute('approve-subject', UserRole::TEACHER));
}
