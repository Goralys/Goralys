import { AuthToken, buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest, parsePhpDateTime } from "@goralys/core";
import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Button } from "@/app/src/ui/button";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";

interface Props {
    publicId: string;
    token: AuthToken;
    onUpdate: () => Promise<void>;
}

export default function AuthTokenCard({ publicId, token, onUpdate }: Props): ReactElement {
    const { showToast } = useToast();
    const { showConfirm } = useConfirm();

    const revoke = async (): Promise<void> => {
        if (!(await showConfirm({ title: "Révocation du jeton", message: "Voulez-vous vraiment révoquer ce jeton d'authentification ?" })))
            return;

        const res = await goralysFetchClient(
            "DELETE",
            buildApiUrl("user/token/any", {
                "csrf-token": await fetchCsrfClient("revoke-auth-token"),
                name: token.name,
                target: publicId,
            }),
            undefined,
            { suppressRedirect: true },
        );

        await handleToastRequest(res, showToast, false);
        await onUpdate();
    };

    return (
        <Card className="grid grid-cols-[1fr_auto] grid-rows-[auto_1fr] gap-x-4">
            <p className="py-2">{token.username}</p>
            <p className="font-bold py-2 text-right">{token.name}</p>

            <div className="flex flex-col mt-2">
                <p className="italic text-sm">Date de création: {parsePhpDateTime(token.created)}</p>
                <p className="italic text-sm">Date d&#39;expiration: {parsePhpDateTime(token.expires)}</p>
            </div>

            <div className="flex items-center">
                <Button text="Révoquer" type="button" color="red" className="w-[105%]! min-w-25 mt-0! mb-0!" onClick={revoke} />
            </div>
        </Card>
    );
}
