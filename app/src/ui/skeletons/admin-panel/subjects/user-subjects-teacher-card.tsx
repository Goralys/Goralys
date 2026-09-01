import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";
import CardTitle from "@/app/src/ui/card-title";

export function UserSubjectsTeacherCardSkeleton(): ReactElement {
    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            <CardTitle title="Sujets" />
            <Card className="flex flex-row w-full">
                <div className="skeleton h-48 w-48 rounded-full shrink-0 mr-2 relative">
                    <div className="absolute inset-[15%] rounded-full bg-sky-200" />
                </div>
                <span className="basis-1/6 grow-0 shrink min-w-0" />
                <ul className="flex flex-col gap-1 self-center mr-10">
                    {Array.from({ length: 4 }).map((_, i) => (
                        <li key={i} className="flex items-center gap-2">
                            <div className="skeleton h-2.5 w-2.5 rounded-full shrink-0" />
                            <div className="skeleton h-4 w-16 rounded-xs" />
                            <div className="skeleton h-4 w-10 rounded-xs ml-auto" />
                        </li>
                    ))}
                </ul>
            </Card>
        </Card>
    );
}
