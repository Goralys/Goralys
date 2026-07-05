"use client";

import { ReactElement, Suspense } from "react";
import { useSupportTicketsWeb } from "@/app/src/hooks/useSupportTicketsWeb";
import SupportTicketCard from "@/app/src/ui/support/support-ticket-card";
import SupportTicketCardSkeleton from "@/app/src/ui/skeletons/support/support-ticket-card";

export default function SupportPageClient(): ReactElement {
    const { supportTickets: tickets } = useSupportTicketsWeb();

    const skeletons = Array.from({ length: 3 }, (_, i) => <SupportTicketCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2">
                <p className="underline text-2xl self-start mb-3">Problèmes dans l&apos;établissement :</p>
                <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                    <div className="flex flex-col gap-2">
                        {tickets === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            tickets?.map((t) => <SupportTicketCard key={t.id + t.reason + t.opener} ticket={t} />)
                        )}
                    </div>
                </Suspense>
            </div>
        </div>
    );
}
