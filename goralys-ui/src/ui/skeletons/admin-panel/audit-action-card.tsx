import { ReactElement } from "react";
import clsx from "clsx";
import { Card } from "#lib/ui/card";
import { CardTitle } from "#lib/ui/card-title";

const ROWS = ["w-40", "w-56", "w-32", "w-48", "w-36"];

export function AuditActionCardSkeleton(): ReactElement {
    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            <CardTitle title="Actions" />
            {ROWS.map((width, i) => (
                <div className="w-full h-5 flex flex-row items-center" key={i}>
                    <div className="skeleton h-3 w-28 rounded-xs shrink-0 mr-2" />
                    <div className={clsx("skeleton h-4 rounded-xs", width)} />
                </div>
            ))}
        </Card>
    );
}
