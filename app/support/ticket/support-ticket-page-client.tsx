"use client";

import { ReactElement, useEffect, useState } from "react";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/lib/fetch/fetch.client";
import { parsePhpDateTime, SupportTicket } from "@/app/lib/types";
import { useSearchParams } from "next/navigation";
import { Card } from "@/app/ui/card";
import { Button } from "@/app/ui/button";
import { TextArea } from "@/app/ui/inputs/text-area";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import { setCookie } from "@/app/lib/cookies";
import Cookies from "universal-cookie";
import { useToast } from "@/app/ui/toast/toast-provider";

export default function SupportTicketPageClient(): ReactElement {
    const searchParams = useSearchParams();
    const toast = useToast();
    const cookies = new Cookies();
    const [message, setMessage] = useState("");
    const [ticket, setTicket] = useState<SupportTicket>({
        id: -1,
        opener: "",
        openerToken: "",
        message: "",
        reason: "other",
        createdAt: { timezone: "", date: "", timezone_type: -1 },
    });

    useEffect(() => {
        const id = searchParams.get("t") ?? -1;

        const run = async (): Promise<void> => {
            const res = await goralysFetchClient(
                buildApiUrl("support/ticket", { "csrf-token": await fetchCsrfClient("get-ticket"), t: id.toString() }, false),
                { method: "GET" },
            );

            if (res.ok) {
                const data = await res.json();
                if (data) setTicket(data);
            }
        };

        run();
    }, [searchParams]);

    const resolve = async (): Promise<void> => {
        const res = await goralysFetchClient(
            buildApiUrl("ticket/resolve", { "csrf-token": await fetchCsrfClient("resolve-ticket"), t: ticket.id.toString() }, false),
            { method: "PATCH", body: JSON.stringify({ message: message ?? "" }) },
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
