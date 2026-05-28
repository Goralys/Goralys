import { Card } from "@/app/ui/card";
import { Button } from "@/app/ui/button";
import React, { ReactElement, useEffect, useState } from "react";
import { fetchCsrfClient } from "@/app/lib/fetch/fetch.client";
import { TextArea } from "@/app/ui/inputs/text-area";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import Cookies from "universal-cookie";

export default function SupportForm(): ReactElement {
    const [csrfToken, setCsrfToken] = useState<string | null>(null);
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/support`;
    const cookie = new Cookies();

    const reasonsConfig = {
        "password-forgot": { label: "Mot de passe oublié" },
        "subject-error": { label: "Question envoyée/validée/rejetée par erreur" },
        other: { label: "Autre (précisez)" },
    } as const;

    type SupportReasons = keyof typeof reasonsConfig;
    const [reason, setReason] = useState<SupportReasons>("password-forgot");

    useEffect(() => {
        const run = async (): Promise<void> => setCsrfToken(await fetchCsrfClient("support-ticket"));

        run().then();
    }, []);

    return (
        <Card className="flex-col min-h-65 bg-sky-200">
            <h1 className="text-xl">Contactez le support Goralys</h1>

            <form className="relative flex flex-col h-full" method="POST" action={requestUrl} autoComplete="on">
                <FloatingInput
                    id="user-email"
                    label="Votre email"
                    helper="Votre email sera simplement utilisé pour vous répondre."
                    defaultValue={cookie.get("email") ?? ""}
                    required
                    email
                />
                <div className="relative pb-0 mb-3">
                    <select
                        className="w-full border-0 border-b-2 border-sky-300 appearance-none
                    cursor-pointer outline-none focus:ring-0 text-base leading-5
                    text-heading pb-0 pr-5 subjects-search-select"
                        value={reason}
                        onChange={(e) => setReason(e.target.value as SupportReasons)}
                    >
                        {Object.entries(reasonsConfig).map(([key, { label }]) => (
                            <option value={key} key={key}>
                                {label}
                            </option>
                        ))}
                    </select>

                    <span
                        className="absolute bottom-0 left-0 w-0 h-0.5 bg-sky-500
                     transition-all duration-300 ease-in-out
                     peer-focus:w-full subjects-search-underline"
                    />
                </div>
                <TextArea id="message" label="Message" helper="Veuillez décrire précisément votre problème." required />

                <input type="hidden" name="csrf-token" value={(csrfToken ? csrfToken : "no-token").trim()} />
                <input type="hidden" name="reason" value={/* reasonsConfig[reason].label */ reason} />

                <Button type="submit" text="Envoyer mon message" className="bottom-0 mt-13" />
            </form>
        </Card>
    );
}
