import { Card } from "@/app/ui/card";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import { Button } from "@/app/ui/button";
import { ReactElement, useEffect, useState } from "react";
import { fetchCsrfClient } from "@/app/lib/fetch/fetch.client";

export default function RegisterForm(): ReactElement {
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/user/register`;

    useEffect(() => {
        const run = async (): Promise<void> => setCsrfToken(await fetchCsrfClient("register"));

        run().then();
    }, []);

    return (
        <Card className="flex-col h-79 bg-sky-200">
            <h1 className="text-xl">Créez votre compte chez Goralys</h1>

            <form className="relative flex flex-col h-full" method="POST" action={requestUrl} autoComplete="on">
                <FloatingInput id="user-name" label="Identifiant" helper="Identifiant au format p.nomX." autocomplete="username" required />
                <FloatingInput id="first-name" label="Prénom" autocomplete="given-name" required />
                <FloatingInput id="last-name" label="Nom de famille" autocomplete="family-name" required />

                <FloatingInput
                    id="password"
                    label="Mot de passe"
                    helper="Choisissez un mot de passe sécurisé."
                    autocomplete="new-password"
                    password
                    required
                />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />

                <Button type="submit" text="Créer mon compte" className="absolute! bottom-0" />
            </form>
        </Card>
    );
}
