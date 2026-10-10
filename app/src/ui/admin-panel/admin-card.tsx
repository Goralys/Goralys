"use client";

import { cookiesGet, PUB_ID_KEY, User } from "@goralys/core";
import { Card } from "@/app/src/ui/card";
import { Button } from "@/app/src/ui/button";
import { ShieldExclamationIcon } from "@heroicons/react/24/outline";
import { ReactElement } from "react";
import { useRouter } from "next/navigation";

interface Props {
    admin: User;
}

export default function AdminCard({ admin }: Props): ReactElement {
    const router = useRouter();

    return (
        <Card className="flex-col w-175! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    <ShieldExclamationIcon width={27.5} className="mr-1.5" />
                    <strong>
                        {admin.fullName} ({admin.username})
                    </strong>
                </div>
                <div className="flex flex-row w-100 gap-1 items-center justify-end">
                    {cookiesGet(PUB_ID_KEY) === admin.publicId ? (
                        <>
                            <p className="ml-auto">(vous)</p>
                            <Button
                                className="w-50!"
                                type="button"
                                text="Consulter votre profil"
                                onClick={() => router.push("/admin/user?u=" + admin.publicId)}
                            />
                        </>
                    ) : (
                        <Button
                            className="w-50!"
                            type="button"
                            text="Gérer l'administrateur"
                            onClick={() => router.push("/admin/user?u=" + admin.publicId)}
                        />
                    )}
                </div>
            </div>
        </Card>
    );
}
