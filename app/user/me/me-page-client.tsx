"use client";

import { Button } from "@/app/src/ui/button";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { emitUserEvent } from "@/app/src/lib/auth/user-event";
import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import Cookies from "universal-cookie";
import { ReactElement, useState } from "react";
import { cacheUserDataClient } from "@/app/src/lib/user/user.client";
import { useEmailModal } from "@/app/src/ui/modals/email/email-modal-provider";
import { EMAIL_KEY, FULL_NAME_KEY, USERNAME_KEY } from "@/app/src/lib/config";

export default function MePageClient(): ReactElement {
    const { showToast } = useToast();
    const { showEmailModal } = useEmailModal();
    const cookies = new Cookies();
    const [email, setEmail] = useState<string>(cookies.get(EMAIL_KEY) ?? "");
    const [username, setUsername] = useState<string>(cookies.get(USERNAME_KEY) ?? "");
    const [fullName, setFullName] = useState<string>(cookies.get(FULL_NAME_KEY) ?? " ");
    const updateUserData = async (): Promise<void> => {
        await cacheUserDataClient();
        setUsername(new Cookies().get(USERNAME_KEY) ?? "");
        setFullName(new Cookies().get(FULL_NAME_KEY) ?? " ");
        setEmail(new Cookies().get(EMAIL_KEY) ?? "");
    };
    const logout = async (): Promise<void> => {
        const payload = { "csrf-token": await fetchCsrfClient("logout") };

        await goralysFetchClient("POST", "user/logout", payload);
        emitUserEvent("logout");

        showToast({
            type: "success",
            title: "Déconnexion",
            message: "Vous avez bien été déconnecté",
        });
    };

    const changeEmail = async (): Promise<void> => {
        const message = email
            ? "Veuillez entrer votre nouvelle adresse mail pour la modifier."
            : "Veuillez entrer une adresse mail pour en ajouter une.";

        const newEmail = await showEmailModal(message);

        if (!newEmail) {
            showToast({
                type: "warning",
                title: "Adresse Mail",
                message: "Veuillez saisir une adresse mail.",
            });
            return;
        }

        const payload = { "csrf-token": await fetchCsrfClient("update-email"), email: newEmail };

        const res = await goralysFetchClient("PUT", "user/email", payload);

        if (res.ok) {
            await updateUserData();
        }

        await handleToastRequest(res, showToast, false);
    };

    const deleteEmail = async (): Promise<void> => {
        const payload = { "csrf-token": await fetchCsrfClient("delete-email") };

        const res = await goralysFetchClient("DELETE", buildApiUrl("user/email", payload));

        if (res.ok) await updateUserData();

        await handleToastRequest(res, showToast, false);
    };

    return (
        <Card className="flex-col absolute top-25 bg-sky-200 left-1/2 -translate-x-1/2 w-100!">
            <p className="underline-offset-1 underline text-2xl">Vos informations:</p>
            <FloatingInput id="username" label="Identifiant" disabled defaultValue={username} />
            <FloatingInput id="firstname" label="Prénom" disabled defaultValue={fullName.split(" ")[0]} />
            <FloatingInput id="lastname" label="Nom" disabled defaultValue={fullName.split(" ").slice(1).join(" ")} />
            <FloatingInput id="email" label="Adresse Mail" email disabled value={email} />
            <Button
                key="change-email-button"
                text={email ? "Changer l'adresse mail" : "Ajouter une adresse mail"}
                type="button"
                onClick={changeEmail}
            />
            {email && (
                <Button
                    key="delete-email-button"
                    className="-mt-0.75!"
                    text="Supprimer l'adresse mail"
                    type="button"
                    onClick={deleteEmail}
                    color="red"
                />
            )}
            <div className="h-px w-12/12 self-center bg-sky-300" />
            <Button key="logout-button" text="Se déconnecter" type="button" onClick={logout} color="red" />
        </Card>
    );
}
