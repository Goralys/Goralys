"use client";

import {
    buildApiUrl,
    cookiesSet,
    fetchCsrfClient,
    goralysFetchClient,
    handleToastRequest,
    HttpMethod,
    parsePhpDateTime,
    separateNames,
    useAuthTokens,
    UserProfile,
} from "@goralys/core";
import { ReactElement } from "react";
import { useAdminPanelSubjects } from "@/app/src/hooks/use-admin-panel-subjects";
import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import UserSubjectsTeacherCard from "@/app/src/ui/admin-panel/subjects/user-subjects-teacher-card";
import UserSubjectsStudentCard from "@/app/src/ui/admin-panel/subjects/user-subjects-student-card";
import CardTitle from "@/app/src/ui/card-title";
import AuthTokenCard from "@/app/src/ui/admin-panel/auth-token-card";
import ReplaceTeacherElement from "@/app/src/ui/admin-panel/replace-teacher-element";
import { usePasswordModal } from "@/app/src/ui/modals/password/password-modal-provider";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Button } from "@/app/src/ui/button";
import { UserSubjectsTeacherCardSkeleton } from "@/app/src/ui/skeletons/admin-panel/subjects/user-subjects-teacher-card";
import { UserSubjectsStudentCardSkeleton } from "@/app/src/ui/skeletons/admin-panel/subjects/user-subjetcs-student-card";

export default function UserPanelPageClient({ profile }: { profile: UserProfile }): ReactElement {
    // may have undesirable side effects, testing to do...
    // useEffect(() => {
    //     const url = new URL(window.location.href);
    //     if (url.searchParams.has("u")) {
    //         url.searchParams.delete("u");
    //         window.history.replaceState(null, "", url.pathname + url.search);
    //     }
    // }, []);
    const password = usePasswordModal();
    const confirmCtx = useConfirm();
    const toast = useToast();
    const { tokens, refetch: refetchTokens, syncKey: tokensSync } = useAuthTokens("admin-panel", profile.pubId);
    const { subjects, refetch: refetchSubjects } = useAdminPanelSubjects(profile.pubId);
    const { firstName, lastName } = separateNames(profile.fullName);

    const onUpdate = async (): Promise<void> => {
        await refetchSubjects();
        await refetchTokens();
    };

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
                target: profile.pubId,
                "admin-password": pwd,
                "csrf-token": csrfToken,
                ...extraPayload,
            }),
        );
        await handleToastRequest(res, toast.showToast, false, toastDuration + 500);
        const data = await res?.json();

        if (data.toastType === "info" && res.ok) {
            cookiesSet(tokensSync, "0");
            await onUpdate();
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

    let roleDisplay = "inconnu";
    switch (profile.role) {
        case "student":
            roleDisplay = "Élève";
            break;
        case "teacher":
            roleDisplay = "Professeur·e";
            break;
        case "admin":
            roleDisplay = "Administrateur·ice";
            break;
    }

    return (
        <div className="flex flex-col grow items-center min-h-screen">
            <div className="flex flex-row mt-5 w-11/12 h-fit max-w-250">
                <Card className="flex flex-col w-5/12! bg-sky-200 m-2 shrink-0">
                    <CardTitle title={profile.id >= 0 ? `Profil (# ${profile.id})` : "Profil (utilisateur non créé)"} />

                    <FloatingInput id="username" label="Nom d'utilisateur" defaultValue={profile.username} disabled />
                    <Button text="Consulter l'identifiant" type="button" onClick={showUsername} className="-mt-1.5!" />
                    <FloatingInput id="first-name" label="Prénom" defaultValue={firstName} disabled />
                    <FloatingInput id="last-name" label="Nom" defaultValue={lastName} disabled />
                    <FloatingInput id="role" label="Rôle" defaultValue={roleDisplay} disabled />
                    <FloatingInput
                        id="email"
                        label="Adresse mail"
                        defaultValue={profile.email.trim() === "" ? "-" : profile.email}
                        disabled
                    />

                    {profile.id >= 0 && <p className="italic text-sm">Date de création: {parsePhpDateTime(profile.createdAt)}</p>}
                </Card>

                {profile.role === "teacher" ? (
                    subjects ? (
                        <UserSubjectsTeacherCard subjects={subjects} />
                    ) : (
                        <UserSubjectsTeacherCardSkeleton />
                    )
                ) : profile.role === "student" ? (
                    subjects ? (
                        <UserSubjectsStudentCard subjects={subjects} />
                    ) : (
                        <UserSubjectsStudentCardSkeleton />
                    )
                ) : (
                    <></>
                )}
            </div>
            <div className="flex flex-row mt-3 w-11/12 max-w-250 h-fit">
                <Card className="flex flex-col flex-1 bg-sky-200 m-2">
                    <CardTitle title="Jetons d'authentification" />
                    {tokens && tokens.length > 0 ? (
                        tokens.map((t) => <AuthTokenCard key={t.name} token={t} />)
                    ) : (
                        <p className="text">Cet utilisateur n&#39;a créé aucun jeton d&#39;authentification.</p>
                    )}
                </Card>
                {profile.role === "teacher" && (
                    <Card className="flex flex-col flex-1 bg-sky-200 m-2">
                        <CardTitle title="Remplacement" />
                        <ReplaceTeacherElement onReplaceAction={replaceTeacher} dropDown={false} />
                    </Card>
                )}
                <Card className="flex flex-col flex-1 bg-sky-200 m-2">
                    <CardTitle title="Zone dangereuse" />
                    {profile.id > 0 && <Button text="Réinitialiser le compte" type="button" onClick={resetAccount} color="red" />}
                    <Button text="Supprimer l'utilisateur" type="button" onClick={deleteUser} color="red" />
                </Card>
            </div>
        </div>
    );
}
