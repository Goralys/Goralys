"use client";

import { Button } from "@/app/ui/button";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { useToast } from "@/app/ui/toast/toast-provider";
import { emitUserEvent } from "@/app/lib/auth/user-event";
import { Card } from "@/app/ui/card";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import Cookies from "universal-cookie";
import { ReactElement, useState } from "react";
import { cacheUserDataClient } from "@/app/lib/user/user.client";
import { useEmailModal } from "@/app/ui/modals/email/password-modal-provider";

export default function MePageClient(): ReactElement {
    const { showToast } = useToast();
    const { showEmailModal } = useEmailModal();
    const cookies = new Cookies();
    const [email, setEmail] = useState<string>(cookies.get("email") ?? "");
    const [username, setUsername] = useState<string>(cookies.get("username") ?? "");
    const [fullName, setFullName] = useState<string>(cookies.get("full-name") ?? " ");
    const updateUserData = async (): Promise<void> => {
        await cacheUserDataClient();
        setUsername(new Cookies().get("username") ?? "");
        setFullName(new Cookies().get("full-name") ?? " ");
        setEmail(new Cookies().get("email") ?? "");
    };
    const logout = async (): Promise<void> => {
        const payload = { "csrf-token": await fetchCsrfClient("logout") };

        await goralysFetchClient("user/logout", {
            method: "POST",
            body: JSON.stringify(payload),
        });
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

        const res = await goralysFetchClient("user/email", {
            method: "PUT",
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            await updateUserData(); // ← Maintenant ça met à jour email ET currentEmail
        }

        const data = await res.json();

        if (data?.toast) {
            showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });
        }
    };

    const deleteEmail = async (): Promise<void> => {
        const payload = { "csrf-token": await fetchCsrfClient("delete-email") };

        const res = await goralysFetchClient("user/email", {
            method: "DELETE",
            body: JSON.stringify(payload),
        });

        if (res.ok) await updateUserData();
        const data = await res.json();

        if (data?.toast) {
            showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });
        }
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
