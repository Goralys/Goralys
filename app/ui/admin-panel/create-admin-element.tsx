"use client";

import { ReactElement, useState } from "react";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import { Button } from "@/app/ui/button";
import { usePasswordModal } from "@/app/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/ui/toast/toast-provider";
import { fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import Cookies from "universal-cookie";

interface CreatAdminElementProps {
    onUpdateAction: () => void;
    syncKey: string;
    virtualSyncKey: string;
}

export default function CreateAdminElement({ onUpdateAction, syncKey, virtualSyncKey }: CreatAdminElementProps): ReactElement {
    const [firstName, setFirstName] = useState("");
    const [lastName, setLastName] = useState("");

    const password = usePasswordModal();
    const toast = useToast();
    const cookies = new Cookies();

    const createAdmin = async (): Promise<void> => {
        const pwd = await password.showPasswordModal();

        if (!pwd) return;

        if (pwd.trim() === "") {
            toast.showToast({
                type: "warning",
                title: "Mot de passe",
                message: "Veuillez saisir un mot de passe.",
            });
            return;
        }

        const csrfToken = await fetchCsrfClient("create-admin");
        const payload = {
            "first-name": firstName,
            "last-name": lastName,
            "admin-password": pwd,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("POST", "admin/create", payload);
        await handleToastRequest(res, toast.showToast, false);
        const data = await res?.json();

        if (data.toastType === "info" && res.ok) {
            cookies.set(syncKey, "0", { path: "/" });
            cookies.set(virtualSyncKey, "0", { path: "/" });
            onUpdateAction();
        }
    };

    return (
        <details className="group/details">
            <summary className="flex flex-row cursor-pointer">
                <svg
                    className="w-5 h-5 text-gray-900 transition group-open/details:rotate-90"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                >
                    <path
                        fillRule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"
                    ></path>
                </svg>
                <span>Créer un administrateur</span>
            </summary>

            <div className="flex flex-col gap-2 mt-2">
                <p className="-mb-2">Entrer le nom du nouvel administrateur</p>
                <div className="flex flex-row min-w-full gap-x-2.5">
                    <FloatingInput id="create-admin-firstname" label="Prénom" onInput={(e) => setFirstName(e.currentTarget.value)} />
                    <FloatingInput id="create-admin-lastname" label="Nom" onInput={(e) => setLastName(e.currentTarget.value)} />
                </div>
            </div>

            <Button type="button" text="Créer" onClick={createAdmin} />
        </details>
    );
}
