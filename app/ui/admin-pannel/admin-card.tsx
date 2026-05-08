'use client';

import { User } from "@/app/lib/types";
import { Card } from "@/app/ui/card";
import { Button } from "@/app/ui/button";
import { ShieldExclamationIcon } from "@heroicons/react/24/outline";
import { usePasswordModal } from "@/app/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/ui/toast/toast-provider";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import Cookies from "universal-cookie";

export default function AdminCard({ admin, onUpdateAction, syncKey }
                                  : { admin: User, onUpdateAction: () => void, syncKey: string }) {
    const password = usePasswordModal();
    const toast = useToast();
    const cookies = new Cookies();

    const fetchAdmin = async (route: string, action: string) => {
        const pwd = await password.showPasswordModal();

        if (!pwd) return;

        if (pwd.trim() === "") {
            toast.showToast({
                type: "warning",
                title: "Mot de passe",
                message: "Veuillez saisir un mot de passe."
            });
            return;
        }

        const csrfToken = await fetchCsrfClient(action);
        const payload = {
            'target': admin.publicId,
            'admin-password': pwd,
            'csrf-token': csrfToken,
        };

        const res = await goralysFetchClient(route, {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        const data = await res?.json();

        if (data?.toast) {
            toast.showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });
        }

        if (data.toastType === 'info' && res.ok) {
            cookies.set(syncKey, "0", { path: '/' });
            onUpdateAction();
        }
    };

    const revokeAccess = async () => await fetchAdmin('admin/revoke', 'revoke-admin');
    return (
        <Card className="flex-col w-175! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    <ShieldExclamationIcon width={27.5} className="mr-1.5" />
                    <strong>{admin.fullName} ({admin.username})</strong>
                </div>
                <div className="flex flex-row w-100 gap-1 justify-end">
                    {
                        cookies.get("public-id") === admin.publicId
                            ? <p className="ml-auto">(vous)</p>
                            : <Button color="red" className="w-50!" type="button" text="Révoquer l'accès" onClick={revokeAccess} />
                    }
                </div>
            </div>
        </Card>
    );
}