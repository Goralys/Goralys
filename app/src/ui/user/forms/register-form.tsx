import { Card } from "@/app/src/ui/card";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { Button } from "@/app/src/ui/button";
import { ReactElement, useEffect, useRef, useState } from "react";
import { fetchCsrfClient } from "@goralys/core";
import { useRouter, useSearchParams } from "next/navigation";

export default function RegisterForm(): ReactElement {
    const searchParams = useSearchParams();
    const router = useRouter();
    const [id, setId] = useState<string | undefined>(undefined);
    const hasRun = useRef(false);
    useEffect(() => {
        if (hasRun.current) return;
        hasRun.current = true;

        const update = (): void => setId(searchParams.get("id") ?? undefined);
        if (!searchParams.get("id")) {
            router.replace("/user/register");
            return;
        }
        update();
        router.replace("/user/register");
    }, [searchParams, router]);

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
                <FloatingInput
                    id="user-name"
                    label="Identifiant"
                    helper="Identifiant au format p.nomX."
                    defaultValue={id}
                    autocomplete="username"
                    required
                />

                <FloatingInput
                    id="password"
                    label="Mot de passe"
                    helper="Choisissez un mot de passe sécurisé."
                    autocomplete="new-password"
                    password
                    required
                />

                <FloatingInput id="email" label="Addresse Mail" autocomplete="email" helper="Ce champ est optionnel." email />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />

                <Button type="submit" text="Créer mon compte" className="absolute! bottom-0" />
            </form>
        </Card>
    );
}
