"use client";

import { useAdmins, User, useVirtualAdmins } from "@goralys/core";
import { ReactElement, Suspense, useEffect, useState } from "react";
import { AdminsSearchBar } from "@/app/src/ui/admin-panel/admins-search-bar";
import AdminCard from "@/app/src/ui/admin-panel/admin-card";
import CreateAdminElement from "@/app/src/ui/admin-panel/create-admin-element";
import AdminPanelCardSkeleton from "@/app/src/ui/skeletons/admin-panel/admin-card";

export default function AdminPanelPageClient(): ReactElement {
    const { users: admins, refetch, syncKey } = useAdmins();
    const [currentAdmins, setCurrentAdmins] = useState<User[] | null>(null);
    // eslint-disable-next-line react-hooks/set-state-in-effect
    useEffect(() => setCurrentAdmins(admins), [admins]);

    const { users: virtualAdmins, refetch: virtualRefetch, syncKey: virtualSyncKey } = useVirtualAdmins();
    const [currentVirtualAdmins, setCurrentVirtualAdmins] = useState<User[] | null>(null);
    // eslint-disable-next-line react-hooks/set-state-in-effect
    useEffect(() => setCurrentVirtualAdmins(virtualAdmins), [virtualAdmins]);

    const skeletons = Array.from({ length: 3 }, (_, i) => <AdminPanelCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div
                className="h-auto w-fit p-2 mt-4 flex flex-col admins-pannel:flex-row admins-pannel:items-start
                            admins-pannel:gap-10"
            >
                <div className="mb-5">
                    <p className="underline text-2xl self-start mb-3">Les administrateurs de l&apos;établissement :</p>
                    <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                        {admins === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            <>
                                <AdminsSearchBar type="real" admins={admins} setCurrentAdminsAction={setCurrentAdmins} />
                                <div className="flex flex-col gap-2">
                                    {currentAdmins?.map((u) => (
                                        <AdminCard key={u.role + u.publicId} admin={u} onUpdateAction={refetch} syncKey={syncKey} />
                                    ))}
                                </div>
                            </>
                        )}
                    </Suspense>
                </div>
                <div>
                    <p className="underline text-2xl self-start mb-3">Administrateurs non créés :</p>
                    <CreateAdminElement
                        onUpdateAction={async () => {
                            await refetch();
                            await virtualRefetch();
                        }}
                        syncKey={syncKey}
                        virtualSyncKey={virtualSyncKey}
                    />
                    <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                        {virtualAdmins === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            <>
                                <AdminsSearchBar type="virtual" admins={virtualAdmins} setCurrentAdminsAction={setCurrentVirtualAdmins} />
                                <div className="flex flex-col gap-2">
                                    {currentVirtualAdmins?.map((u) => (
                                        <AdminCard
                                            key={u.role + u.publicId + "-virtual"}
                                            admin={u}
                                            onUpdateAction={virtualRefetch}
                                            syncKey={virtualSyncKey}
                                        />
                                    ))}
                                </div>
                            </>
                        )}
                    </Suspense>
                </div>
            </div>
        </div>
    );
}
