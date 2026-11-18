<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\App\HTTP\Middleware\MiddlewareSets;
use Goralys\App\Router\Routes;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Kernel\GoralysKernel;

// ==================================================
// [SECTION] Topics actions
// ==================================================
Routes::post('topics/import', function (GoralysKernel $kernel) {
    $topics = $kernel->topics->makeTopicsFromZip($kernel->fileManager->get("topics-file"));

    foreach ($topics as $topic) {
        if (!$kernel->topics->insert($topic)) {
            $kernel->db->rollback();
            $kernel->deferredResponse(500)->error( // Internal Server Error
                "Une erreur interne est survenue lors de l'insertion des sujets.",
            )
            ->redirect("/subject/")
            ->send();
        }
    }

    $kernel->db->commit();

    $file = $kernel->topics->exportUsernames($topics);
    $kernel->response()->download($file, "utilisateurs.txt", after: fn() => @unlink($file));
})
        ->middlewares(...MiddlewareSets::topicsRoute('import-topics'));

Routes::delete('topics', function (GoralysKernel $kernel) {
    if (!$kernel->topics->clear() || !$kernel->users->clear()) {
        $kernel->db->rollback();
        $kernel->deferredResponse(500)->error( // Internal Server Error
            "Les utilisateurs ou les sujets n'ont pas pu être supprimés, veuillez réessayer ultérieurement.",
        )
        ->redirect("/subject/")
        ->send();
    }

    $kernel->db->commit();
    $kernel->deferredResponse()->toast(
        ToastType::SUCCESS,
        "Suppression des sujets",
        "Tous les sujets et les utilisateurs ont été supprimés (sauf administrateurs).",
    )
    ->redirect("/subject/")
    ->send();
})
        ->middlewares(...MiddlewareSets::topicsRoute('delete-topics'));
