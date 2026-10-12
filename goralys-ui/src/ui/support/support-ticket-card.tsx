"use client";

import { buildApiUrl, reasonsConfig, SupportTicket } from "@goralys/core";
import React, { ReactElement } from "react";
import { TextArea } from "#lib/ui/inputs/text-area";
import { Button } from "#lib/ui/button";
import { useGoralysUI } from "@goralys/ui";

interface Props {
    ticket: SupportTicket;
}

export function SupportTicketCard({ ticket }: Props): ReactElement {
    const router = useGoralysUI().useRouter();

    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 gap-y-3 p-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <span>
                    <strong>#{ticket.id}</strong> [{reasonsConfig[ticket.reason].label}]
                </span>

                <a
                    href={buildApiUrl("ticket/contact", { t: ticket.id.toString() }, true)}
                    target="_blank"
                    title="Contactez l'utilisateur"
                    className="cursor-pointer"
                >
                    <strong>{ticket.opener}</strong>
                </a>
            </div>

            <TextArea
                key={"support-message-" + ticket.id + ticket.opener}
                id="message"
                label="Message"
                defaultValue={ticket.message}
                disabled
            />
            <Button
                text="Ce problème a été résolu"
                type="button"
                onClick={() => {
                    router.push("/support/ticket?t=" + encodeURIComponent(ticket.id));
                }}
            />
        </div>
    );
}
