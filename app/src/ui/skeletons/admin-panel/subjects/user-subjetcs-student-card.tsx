import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";

export function UserSubjectsStudentCardSkeleton(): ReactElement {
    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            {Array.from({ length: 2 }).map((_, i) => (
                <Card className="flex flex-row w-full justify-between" key={i}>
                    <div className="flex flex-col gap-1.5">
                        <div className="skeleton h-5 w-16 rounded-xs" />
                        <div className="skeleton h-4 w-24 rounded-xs" />
                    </div>
                    <div className="skeleton h-12 w-12 rounded-full shrink-0 relative">
                        <div className="absolute inset-[15%] rounded-full bg-sky-200" />
                    </div>
                </Card>
            ))}
        </Card>
    );
}
