import { AuditAction, parsePhpDateTime } from "@goralys/core";
import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";
import CardTitle from "@/app/src/ui/card-title";

interface Props {
    actions: AuditAction[] | null;
}

export default function AuditActionCard({ actions }: Props): ReactElement {
    if (!actions) return <></>;

    if (actions.length === 0) {
        return (
            <Card className="flex flex-col grow bg-sky-200 m-2">
                <CardTitle title="Actions" />
                <p>Aucune action enregistrée pour cet administrateur</p>
            </Card>
        );
    }

    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            <CardTitle title="Actions" />
            {actions.map((a) => (
                <div className="w-full h-5 flex flex-row items-center" key={a.initiator + parsePhpDateTime(a.at)}>
                    <p className="mr-2 text-xs font-mono">{parsePhpDateTime(a.at)}</p>
                    <p>{a.description}</p>
                </div>
            ))}
        </Card>
    );
}
