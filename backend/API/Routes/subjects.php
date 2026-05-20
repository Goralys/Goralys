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
    $router->get('subjects/admin', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::ADMIN);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-admin-subjects', UserRole::ADMIN));

    $router->get('subjects/teacher', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::TEACHER);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-teacher-subjects', UserRole::TEACHER));

    $router->get('subjects/student', function (GoralysKernel $kernel) {
        $result = $kernel->subjects->getForRole(UserRole::STUDENT);
        $kernel->response()->json($result);
    })
            ->middlewares(...MiddlewareSets::subjectsRoute('get-student-subjects', UserRole::STUDENT));

    // -------------------------
    // [SUB SECTION] Get draft
    // -------------------------
    $router->get(
        'subjects/draft',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $path = $kernel->subjects->draftsManager->getPath(
                $kernel->usernameManager->get($request->param("student")),
                $kernel->usernameManager->get($request->param("teacher")),
                $request->param("topic"),
            );

            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $kernel->response()->download($path, $request->param("file-name") . "." . $extension);
        },
        ...RouterOptions::$INPUT::require("teacher", "student", "topic", "file-name"),
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

    $router->patch(
        'subjects/status',
        function (GoralysKernel $kernel, RequestInterface $request) {
            if (!$kernel->users->validatePassword($request->param("admin-password"))) {
                $kernel->deferredResponse(501)->toast( // Unauthorized
                    ToastType::WARNING,
                    "Mot de passe",
                    "Veuillez saisir le bon mot de passe",
                )
                        ->redirect("/subject")
                        ->send();
            }

            $kernel->subjects->updateField(
                $kernel->usernameManager->get($request->param('teacher')),
                $kernel->usernameManager->get($request->param('student')),
                $request->param("topic"),
                SubjectFields::STATUS,
                SubjectStatus::fromString($request->param("status"))
            );

            $kernel->deferredResponse()->toast(
                ToastType::INFO,
                "Statut",
                "Le statut de la question a bien été mis à jour."
            )
                    ->redirect("/subject")
                    ->send();
        },
        ...RouterOptions::$INPUT::require("status", "topic", "teacher", "student", "admin-password")
    )
        ->middlewares(...MiddlewareSets::subjectsRoute('update-subject-status', UserRole::ADMIN));

    // -------------------------
    // [SUB SECTION] Student
    // -------------------------
    $router->put(
        'subjects/draft',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'student')?->send();

            $result = $kernel->subjects->updateField(
                $kernel->usernameManager->get($request->param('teacher')),
                $kernel->usernameManager->get($request->param('student')),
                $request->param('topic'),
                SubjectFields::SUBJECT,
                $request->param('draft'),
                (bool) $request->param('interdisciplinary'),
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
        ...RouterOptions::$INPUT::require("draft", "topic", "teacher", "student"),
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
            $kernel->guard->matchCurrentUser($request, 'student')?->send();

            $teacherUsername = $kernel->usernameManager->get($request->param('teacher'));
            $studentUsername = $kernel->usernameManager->get($request->param('student'));
            $topic = $request->param('topic');
            $subject = $request->param('subject');
            $interdisciplinary = (bool) $request->param('interdisciplinary');
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
                $kernel->deferredResponse(500)->error( // Internal server error
                    "Une erreur interne est survenue lors de l'enregistrement de votre question, 
                    veuillez réessayer ultérieurement.",
                )
                    ->send();
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
                $kernel->deferredResponse(409)->error( // Conflict
                    "Votre question n'a pas pu être envoyée. Veuillez réessayer ultérieurement.",
                )
                    ->send();
            }

            if ($draftFile) {
                $updateResult = $kernel->subjects->draftsManager->update(
                    $studentUsername,
                    $teacherUsername,
                    $topic,
                );
            } else {
                // We delete the old draft if present.
                $updateResult = $kernel->subjects->draftsManager->flush(
                    $studentUsername,
                    $teacherUsername,
                    $topic,
                );
            }

            if (!$updateResult) {
                $kernel->db->rollback();
                $kernel->deferredResponse(500)->error( // Internal server error
                    "Votre question n'a pas pu être envoyée, car votre brouillon n'a pas pu être enregistré. 
                    Veuillez réessayer ultérieurement.",
                )
                    ->send();
            }

            $kernel->db->commit();
            $kernel->deferredResponse()->toast(
                ToastType::INFO,
                "Question",
                "Votre question a bien été envoyée.",
            )
                ->send();
        },
        ...RouterOptions::$INPUT::require("subject", "topic", "teacher", "student"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de l'envoi de votre question, veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
            ->middlewares(...MiddlewareSets::subjectsRoute('submit-subject', UserRole::STUDENT, transaction: true));

    // -------------------------
    // [SUB SECTION] Teacher
    // -------------------------
    $router->post(
        'subjects/reject',
        function (GoralysKernel $kernel, RequestInterface $request) {
            $kernel->guard->matchCurrentUser($request, 'teacher')?->send();

            $teacherUsername = $kernel->usernameManager->get($request->param('teacher'));
            $studentUsername = $kernel->usernameManager->get($request->param('student'));
            $topic = $request->param('topic');
            $comment = $request->param('comment');
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
        ...RouterOptions::$INPUT::require("comment", "topic", "teacher", "student"),
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
            $kernel->guard->matchCurrentUser($request, 'teacher')?->send();

            $result = $kernel->subjects->updateField(
                $kernel->usernameManager->get($request->param('teacher')),
                $kernel->usernameManager->get($request->param('student')),
                $request->param("topic"),
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
        ...RouterOptions::$INPUT::require("topic", "teacher", "student"),
        ...RouterOptions::$INPUT::onFailure(
            "Une erreur interne est survenue lors de la validation de la question, 
                veuillez réessayer ultérieurement.",
            "/subject/",
        ),
    )
    ->middlewares(...MiddlewareSets::subjectsRoute('approve-subject', UserRole::TEACHER));
}
