"use client";

import {
    buildApiUrl,
    cookiesSet,
    fetchCsrfClient,
    goralysFetchClient,
    handleToastRequest,
    HttpMethod,
    User,
    USER_SYNCS,
} from "@goralys/core";
import { Card } from "@/app/src/ui/card";
import { Button } from "@/app/src/ui/button";
import { AcademicCapIcon, BookOpenIcon } from "@heroicons/react/24/outline";
import { usePasswordModal } from "@/app/src/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import ReplaceTeacherElement from "./replace-teacher-element";
import { ReactElement } from "react";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";

interface UserCardProps {
    user: User;
    type: "real" | "virtual";
    onUpdateAction: () => void;
    syncKey: string;
    virtualSyncKey: string;
}

export default function UserCard({ user, type, onUpdateAction, syncKey, virtualSyncKey }: UserCardProps): ReactElement {
    const password = usePasswordModal();
    const confirmCtx = useConfirm();
    const toast = useToast();

    const fetchAdmin = async (
        route: string,
        method: HttpMethod,
        action: string,
        confirm: string,
        extraPayload: Record<string, string> = {},
        toastDuration: number = 5000,
        confirmModal: boolean = false,
    ): Promise<void> => {
        if (confirmModal) {
            const conf = await confirmCtx.showConfirm({ message: "Veuillez confirmer " + confirm, title: "Confirmation requise" });
            if (!conf) return;
        }

        const pwd = await password.showPasswordModal(confirm);
        if (!pwd) return;

        if (pwd.trim() === "") {
            toast.showToast({
                type: "warning",
                title: "Mot de passe",
                message: "Veuillez saisir un mot de passe.",
            });
            return;
        }
        const csrfToken = await fetchCsrfClient(action);

        const res = await goralysFetchClient(
            method,
            buildApiUrl(route, {
                target: user.publicId,
                "admin-password": pwd,
                "csrf-token": csrfToken,
                ...extraPayload,
            }),
        );
        await handleToastRequest(res, toast.showToast, false, toastDuration + 500);
        const data = await res?.json();

        if (data.toastType === "info" && res.ok) {
            cookiesSet(syncKey, "0");
            cookiesSet(virtualSyncKey, "0");

            // Invalidate caches
            cookiesSet(USER_SYNCS["users-real"], "0");
            cookiesSet(USER_SYNCS["users-virtual"], "0");
            onUpdateAction();
        }
    };

    const resetAccount = async (): Promise<void> =>
        await fetchAdmin("users/reset", "PATCH", "reset-account", "la réinitialisation du compte");

    const deleteUser = async (): Promise<void> =>
        await fetchAdmin("users", "DELETE", "delete-user", "la suppression de l'utilisateur", undefined, 500, true);

    const replaceTeacher = async (firstName: string, lastName: string): Promise<void> =>
        await fetchAdmin("users/teacher/replace", "PUT", "replace-teacher", "le remplacement du professeur", {
            "first-name": firstName,
            "last-name": lastName,
        });

    const showUsername = async (): Promise<void> =>
        await fetchAdmin("users/username", "GET", "get-username", "la révélation de l'identifiant", {}, 10 * 1000);

    return (
        <Card className="flex-col w-200! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    {
                        // No admins here.
                        user.role == "teacher" ? (
                            <BookOpenIcon width={27.5} className="mr-1.5" />
                        ) : (
                            <AcademicCapIcon width={27.5} className="mr-1.5" />
                        )
                    }
                    <button title="Consulter l'identifiant" className="cursor-pointer" onClick={showUsername}>
                        <strong>
                            {user.fullName.length > 25 ? user.fullName.substring(0, 24) + "..." : user.fullName} ({user.username})
                        </strong>
                    </button>
                </div>
                <div className="flex flex-row w-100 gap-1 place-content-end">
                    {type === "real" && <Button type="button" className="w-55!" text="Réinitialiser le compte" onClick={resetAccount} />}
                    <Button color="red" className="w-45!" type="button" text="Supprimer" onClick={deleteUser} />
                </div>
            </div>
            {user.role == "teacher" && <ReplaceTeacherElement onReplaceAction={replaceTeacher} />}
        </Card>
    );
}
