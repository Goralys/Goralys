import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { Button } from "@/app/src/ui/button";
import { ReactElement, useEffect, useState } from "react";
import { fetchCsrfClient } from "@goralys/core";

export default function LoginForm(): ReactElement {
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/user/login/`;

    useEffect(() => {
        const run = async (): Promise<void> => setCsrfToken(await fetchCsrfClient("login"));

        run().then();
    }, []);

    return (
        <Card className="relative flex-col h-65 bg-sky-200">
            <h1 className="text-xl">Connectez vous à votre compte Goralys</h1>

            <form className="relative flex flex-col h-full" action={requestUrl} method="POST" autoComplete="on">
                <FloatingInput id="username" label="Identifiant" helper="Identifiant au format p.nomX" autocomplete="username" required />

                <FloatingInput id="password" label="Mot de passe" autocomplete="current-password" password required />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />

                <Button type="submit" text="Se connecter" className="absolute! bottom-0" />
            </form>
        </Card>
    );
}
