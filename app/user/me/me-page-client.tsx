"use client";

import { Button } from "@/app/src/ui/button";
import {
    buildApiUrl,
    cacheUserDataClient,
    cookiesGet,
    EMAIL_KEY,
    emitUserEvent,
    fetchCsrfClient,
    FULL_NAME_KEY,
    goralysFetchClient,
    handleToastRequest,
    USERNAME_KEY,
} from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { ReactElement, useState } from "react";
import { useEmailModal } from "@/app/src/ui/modals/email/email-modal-provider";

export default function MePageClient(): ReactElement {
    const { showToast } = useToast();
    const { showEmailModal } = useEmailModal();

    const [email, setEmail] = useState<string>((cookiesGet(EMAIL_KEY) ?? "") as string);
    const [username, setUsername] = useState<string>((cookiesGet(USERNAME_KEY) ?? "") as string);
    const [fullName, setFullName] = useState<string>((cookiesGet(FULL_NAME_KEY) ?? " ") as string);
    const updateUserData = async (): Promise<void> => {
        await cacheUserDataClient();
        setUsername((cookiesGet(EMAIL_KEY) ?? "") as string);
        setFullName((cookiesGet(USERNAME_KEY) ?? "") as string);
        setEmail((cookiesGet(FULL_NAME_KEY) ?? " ") as string);
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
