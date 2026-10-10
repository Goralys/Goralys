import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { Button } from "@/app/src/ui/button";
import { ReactElement, SyntheticEvent, useEffect, useRef, useState } from "react";
import { fetchCsrfClient } from "@goralys/core";
import { useMediaQuery } from "@/app/src/hooks/use-media-query";

export default function LoginForm(): ReactElement {
    const isDesktop = useMediaQuery("(min-width: 640px)");
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState<boolean>(false);
    const submittingRef = useRef(false);
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/user/login?high-school-token=${process.env.NEXT_PUBLIC_API_TOKEN}`;

    useEffect(() => {
        const canceled = false;
        fetchCsrfClient("login")
            .then((token) => {
                if (!canceled) setCsrfToken(token);
            })
            .catch(() => {
                if (!canceled) setCsrfToken(null);
            });
    }, []);

    function handleSubmit(e: SyntheticEvent<HTMLFormElement, SubmitEvent>): void {
        if (submittingRef.current) {
            e.preventDefault();
            return;
        }
        submittingRef.current = true;
        setSubmitting(true);
    }

    return (
        <Card className="relative flex-col h-65 bg-sky-200 order-1 sm:order-2">
            <h1 className="text-xl">Connectez vous à votre compte Goralys</h1>

            <form className="relative flex flex-col h-full" action={requestUrl} method="POST" autoComplete="on" onSubmit={handleSubmit}>
                <FloatingInput id="username" label="Identifiant" helper="Identifiant au format p.nomX" autocomplete="username" required />

                <FloatingInput id="password" label="Mot de passe" autocomplete="current-password" password required />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />
                <input type="hidden" name="client_context" value={isDesktop ? "desktop" : "mobile"} />

                <Button
                    type="submit"
                    text={csrfToken === null ? "Chargement..." : submitting ? "Connexion..." : "Se connecter"}
                    className="absolute! bottom-0"
                    disabled={csrfToken === null || submitting}
                />
            </form>
        </Card>
    );
}
