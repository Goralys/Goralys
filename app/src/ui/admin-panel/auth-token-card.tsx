import { AuthToken, parsePhpDateTime } from "@goralys/core";
import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";

interface Props {
    token: AuthToken;
}

export default function AuthTokenCard({ token }: Props): ReactElement {
    return (
        <Card className="flex flex-col">
            <div className="flex flex-row justify-between">
                <p>{token.username}</p>
                <p className="font-bold">{token.name}</p>
            </div>
            <p className="italic text-sm">Date de création: {parsePhpDateTime(token.created)}</p>
            <p className="italic text-sm">Date d&#39;expiration: {parsePhpDateTime(token.expires)}</p>
        </Card>
    );
}
