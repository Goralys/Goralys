import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { Button } from "@/app/src/ui/button";
import { ReactElement, useEffect, useState } from "react";
import { fetchCsrfClient } from "@goralys/core";
import { useMediaQuery } from "@/app/src/hooks/use-media-query";

export default function LoginForm(): ReactElement {
    const isDesktop = useMediaQuery("(min-width: 640px)");
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/user/login/`;

    useEffect(() => {
        const run = async (): Promise<void> => setCsrfToken(await fetchCsrfClient("login"));

        run().then();
    }, []);

    return (
        <Card className="relative flex-col h-65 bg-sky-200 order-1 sm:order-2">
            <h1 className="text-xl">Connectez vous à votre compte Goralys</h1>

            <form className="relative flex flex-col h-full" action={requestUrl} method="POST" autoComplete="on">
                <FloatingInput id="username" label="Identifiant" helper="Identifiant au format p.nomX" autocomplete="username" required />

                <FloatingInput id="password" label="Mot de passe" autocomplete="current-password" password required />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />
                <input type="hidden" name="client_context" value={isDesktop ? "desktop" : "mobile"} />
                <input type="hidden" name="high-school-token" value={process.env.NEXT_PUBLIC_API_TOKEN} />

                <Button
                    type="submit"
                    text={csrfToken === null ? "Chargement..." : "Se connecter"}
                    className="absolute! bottom-0"
                    disabled={csrfToken === null}
                />
            </form>
        </Card>
    );
}
