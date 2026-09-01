"use client";

import { buildApiUrl, reasonsConfig, SupportTicket } from "@goralys/core";
import React, { ReactElement } from "react";
import { TextArea } from "@/app/src/ui/inputs/text-area";
import { Button } from "@/app/src/ui/button";

interface Props {
    ticket: SupportTicket;
}

export default function SupportTicketCard({ ticket }: Props): ReactElement {
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
                    window.location.href = "/support/ticket?t=" + ticket.id;
                }}
            />
        </div>
    );
}
