"use client";

import { ReactElement, useState } from "react";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import { parsePhpDateTime, SupportTicket } from "@/app/src/lib/types";
import { Card } from "@/app/src/ui/card";
import { Button } from "@/app/src/ui/button";
import { TextArea } from "@/app/src/ui/inputs/text-area";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { setCookie } from "@/app/src/lib/cookies";
import Cookies from "universal-cookie";
import { useToast } from "@/app/src/ui/toast/toast-provider";

interface SupportTicketPageClientProps {
    ticket: SupportTicket;
}

export default function SupportTicketPageClient({ ticket }: SupportTicketPageClientProps): ReactElement {
    const toast = useToast();
    const cookies = new Cookies();
    const [message, setMessage] = useState("");

    const resolve = async (): Promise<void> => {
        const res = await goralysFetchClient(
            "PATCH",
            buildApiUrl("ticket/resolve", { "csrf-token": await fetchCsrfClient("resolve-ticket"), t: ticket.id.toString() }),
            { message: message ?? "" },
        );

        if (res.ok) setCookie(cookies, "support-tickets-synced", "0");
        await handleToastRequest(res, toast.showToast);
    };

    return (
        <div className="flex grow justify-center bg-gray-50 w-200 p-4 min-h-screen">
            <Card className="w-full max-w-2xl flex flex-col gap-4 mt-12 bg-sky-200 h-fit">
                <div className="border-b pb-4">
                    <h1 className="text-2xl font-bold">Ticket #{ticket.id}</h1>
                </div>

                <div className="flex flex-col gap-y-1">
                    <FloatingInput id="utilisateur" label="Utilisateur" value={ticket.opener} disabled />

                    <FloatingInput id="raison" label="Raison" value={ticket.reason} disabled />

                    <TextArea id="message" label="Message" defaultValue={ticket.message} disabled />

                    <FloatingInput id="date" label="Date de création" defaultValue={parsePhpDateTime(ticket.createdAt)} disabled />
                </div>

                <TextArea
                    id="resolution-message"
                    label="Message de résolution"
                    defaultValue={message}
                    onChangeAction={(e) => setMessage(e.target.value)}
                />
                <Button text="Résoudre le ticket" type="button" onClick={resolve} color="green" />
            </Card>
        </div>
    );
}
