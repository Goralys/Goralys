"use client";

import { Button } from "@/app/ui/button";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { useToast } from "@/app/ui/toast/toast-provider";
import { emitUserEvent } from "@/app/lib/auth/user-event";
import { Card } from "@/app/ui/card";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import Cookies from "universal-cookie";
import { ReactElement } from "react";

export default function MePageClient(): ReactElement {
    const { showToast } = useToast();
    async function logout(): Promise<void> {
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
    }

    const cookies = new Cookies();
    const username: string = cookies.get("username") ?? "";
    const fullName: string = cookies.get("full-name") ?? " ";

    return (
        <Card className="flex-col absolute top-25 bg-sky-200 left-1/2 -translate-x-1/2 w-100!">
            <p className="underline-offset-1 underline text-2xl">Vos informations:</p>
            <FloatingInput id="username" label="Identifiant" disabled defaultValue={username} />
            <FloatingInput id="firstname" label="Prénom" disabled defaultValue={fullName.split(" ")[0]} />
            <FloatingInput id="lastname" label="Nom" disabled defaultValue={fullName.split(" ").slice(1).join(" ")} />
            <Button key="logout-button" text="Se déconnecter" type="button" onClick={logout} />
        </Card>
    );
}
