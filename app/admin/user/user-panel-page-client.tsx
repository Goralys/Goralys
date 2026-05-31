"use client";

import { useUsers, useVirtualUsers } from "@/app/src/hooks/useUsers";
import UserCard from "../../ui/admin-panel/user-card";
import { ReactElement, Suspense, useState } from "react";
import { User } from "@/app/src/lib/types";
import { UsersSearchBar } from "../../ui/admin-panel/users-search-bar";
import UserCardSkeleton from "../../ui/skeletons/admin-panel/user-card";

export default function UserPanelPageClient(): ReactElement {
    const { users, refetch, syncKey } = useUsers();
    const [currentUsers, setCurrentUsers] = useState<User[] | null>(null);

    const { users: virtualUsers, refetch: virtualRefetch, syncKey: virtualSyncKey } = useVirtualUsers();
    const [currentVirtualUsers, setCurrentVirtualUsers] = useState<User[] | null>(null);

    const skeletons = Array.from({ length: 3 }, (_, i) => <UserCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div
                className="h-auto w-fit p-2 mt-4 flex flex-col users-pannel:flex-row users-pannel:items-start
                            users-pannel:gap-10"
            >
                <div className="mb-5">
                    <p className="underline text-2xl self-start mb-3">Les utilisateurs de l&apos;établissement :</p>
                    <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                        {users === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            <>
                                <UsersSearchBar type="real" users={users} setCurrentUsers={setCurrentUsers} />
                                <div className="flex flex-col gap-2">
                                    {currentUsers?.map((u) => (
                                        <UserCard
                                            type="real"
                                            key={u.role + u.publicId}
                                            user={u}
                                            onUpdateAction={refetch}
                                            syncKey={syncKey}
                                            virtualSyncKey={virtualSyncKey}
                                        />
                                    ))}
                                </div>
                            </>
                        )}
                    </Suspense>
                </div>
                <div>
                    <p className="underline text-2xl self-start mb-3">Utilisateurs non créés :</p>
                    <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                        {virtualUsers === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            <>
                                <UsersSearchBar type="virtual" users={virtualUsers} setCurrentUsers={setCurrentVirtualUsers} />
                                <div className="flex flex-col gap-2">
                                    {currentVirtualUsers?.map((u) => (
                                        <UserCard
                                            type="virtual"
                                            key={u.role + u.publicId + "-virtual"}
                                            user={u}
                                            onUpdateAction={virtualRefetch}
                                            syncKey={syncKey}
                                            virtualSyncKey={virtualSyncKey}
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
