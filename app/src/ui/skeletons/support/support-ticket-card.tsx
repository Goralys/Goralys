import { ReactElement } from "react";

export default function SupportTicketCardSkeleton(): ReactElement {
    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 gap-y-3 p-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <div className="skeleton h-5 w-32 rounded-xs" />
                <div className="skeleton h-5 w-24 rounded-xs" />
            </div>

            <div className="skeleton h-24 w-full rounded-xs" />

            <div className="skeleton h-10 w-full rounded-xs" />
        </div>
    );
}
