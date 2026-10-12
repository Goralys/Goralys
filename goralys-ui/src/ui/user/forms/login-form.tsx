import { Card } from "#lib/ui/card";
import { FloatingInput } from "#lib/ui/inputs/floating-input";
import { Button } from "#lib/ui/button";
import { ReactElement, SyntheticEvent, useEffect, useRef, useState } from "react";
import { fetchCsrfClient, getGoralysClientConfig, goralysFetchClient, handleToastRequest } from "@goralys/core";
import { useMediaQuery } from "#lib/hooks/use-media-query";
import { useToast } from "#lib/ui/toast/toast-provider";

export function LoginForm(): ReactElement {
    const { apiDomain, getSchoolToken } = getGoralysClientConfig();
    const { showToast } = useToast();

    const isDesktop = useMediaQuery("(min-width: 640px)");
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState<boolean>(false);
    const submittingRef = useRef(false);
    const requestUrl = `user/login?high-school-token=${getSchoolToken()}`;

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

    const submitDesktop = (e: SyntheticEvent<HTMLFormElement, SubmitEvent>): void => {
        if (submittingRef.current) {
            e.preventDefault();
            return;
        }
        submittingRef.current = true;
        setSubmitting(true);
    };

    const submitMobile = async (e: SyntheticEvent<HTMLFormElement, SubmitEvent>): Promise<void> => {
        e.preventDefault();

        if (submittingRef.current) return;
        submittingRef.current = true;
        setSubmitting(true);

        const data = new FormData(e.currentTarget);
        try {
            const res = await goralysFetchClient("POST", requestUrl, {
                "csrf-token": csrfToken,
                client_context: "mobile",
                username: String(data.get("username") ?? ""),
                password: String(data.get("password") ?? ""),
            });

            await handleToastRequest(res, showToast, true);
        } finally {
            submittingRef.current = false;
            setSubmitting(false);
        }
    };

    function handleSubmit(e: SyntheticEvent<HTMLFormElement, SubmitEvent>): void {
        if (getGoralysClientConfig().isNative) {
            submitMobile(e).then();
            return;
        }

        submitDesktop(e);
    }

    return (
        <Card className="relative flex-col h-65 bg-sky-200 order-1 sm:order-2">
            <h1 className="text-xl">Connectez vous à votre compte Goralys</h1>

            <form
                className="relative flex flex-col h-full"
                action={`${apiDomain}/${requestUrl}`}
                method="POST"
                autoComplete="on"
                onSubmit={handleSubmit}
            >
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
