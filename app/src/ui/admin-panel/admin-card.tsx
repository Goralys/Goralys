"use client";

import { HttpMethod, User } from "@/app/src/lib/types";
import { Card } from "@/app/src/ui/card";
import { Button } from "@/app/src/ui/button";
import { ShieldExclamationIcon } from "@heroicons/react/24/outline";
import { usePasswordModal } from "@/app/src/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import Cookies from "universal-cookie";
import { ReactElement } from "react";
import { PUB_ID_KEY, USER_SYNCS } from "@/app/src/lib/config";

interface AdminCardProps {
    admin: User;
    onUpdateAction: () => void;
    syncKey: string;
}

export default function AdminCard({ admin, onUpdateAction, syncKey }: AdminCardProps): ReactElement {
    const password = usePasswordModal();
    const toast = useToast();
    const cookies = new Cookies();

    const fetchAdmin = async (route: string, method: HttpMethod, action: string, confirm?: string): Promise<void> => {
        const pwd = await password.showPasswordModal(confirm);

        if (!pwd) return;

        if (pwd.trim() === "") {
            toast.showToast({
                type: "warning",
                title: "Mot de passe",
                message: "Veuillez saisir un mot de passe.",
            });
            return;
        }

        const csrfToken = await fetchCsrfClient(action);

        const res = await goralysFetchClient(
            method,
            buildApiUrl(route, {
                target: admin.publicId,
                "admin-password": pwd,
                "csrf-token": csrfToken,
            }),
        );
        await handleToastRequest(res, toast.showToast, false);
        const data = await res?.json();

        if (data.toastType === "info" && res.ok) {
            cookies.set(syncKey, "0", { path: "/" });

            // Invalidate caches
            cookies.set(USER_SYNCS["admins-real"], "0", { path: "/" });
            cookies.set(USER_SYNCS["admins-virtual"], "0", { path: "/" });
            onUpdateAction();
        }
    };

    const revokeAccess = async (): Promise<void> =>
        await fetchAdmin("admin/revoke", "DELETE", "revoke-admin", "la révocation de l'administrateur");
    return (
        <Card className="flex-col w-175! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    <ShieldExclamationIcon width={27.5} className="mr-1.5" />
                    <strong>
                        {admin.fullName} ({admin.username})
                    </strong>
                </div>
                <div className="flex flex-row w-100 gap-1 justify-end">
                    {cookies.get(PUB_ID_KEY) === admin.publicId ? (
                        <p className="ml-auto">(vous)</p>
                    ) : (
                        <Button color="red" className="w-50!" type="button" text="Révoquer l'accès" onClick={revokeAccess} />
                    )}
                </div>
            </div>
        </Card>
    );
}
