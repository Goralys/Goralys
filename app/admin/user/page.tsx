'use client';

// import {Button} from "@/app/ui/button";
import {useUsers, useVirtualUsers} from "@/app/hooks/useUsers";
import UserCard from "@/app/ui/admin-pannel/user-card";
import {useState, Suspense} from "react";
import {User} from "@/app/lib/types";
import {UsersSearchBar} from "@/app/ui/admin-pannel/users-search-bar";
import UserCardSkeleton from "@/app/ui/skeletons/admin-pannel/user-card";

export default function Page() {
    const {users, refetch, syncKey} = useUsers();
    const [currentUsers, setCurrentUsers] = useState<User[] | null>(null);

    const {users: virtualUsers, refetch: virtualRefetch, syncKey: virtualSyncKey} = useVirtualUsers();
    const [currentVirtualUsers, setCurrentVirtualUsers] = useState<User[] | null>(null);

    const skeletons = Array.from({ length: 3 }, (_, i) => <UserCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2 mt-4 flex flex-col 3xl:flex-row 3xl:items-start 3xl:gap-10">
                <div className="mb-5">
                    <p className="underline text-2xl self-start mb-3">Les utilisateurs de l&apos;établissement :</p>
                    <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                        {users === null ? (
                            <div className="flex flex-col gap-2">{skeletons}</div>
                        ) : (
                            <>
                                <UsersSearchBar users={users} setCurrentUsers={setCurrentUsers}/>
                                <div className="flex flex-col gap-2">
                                    {currentUsers?.map((u) => (
                                        <UserCard key={u.role + u.publicId} user={u} onUpdateAction={refetch} syncKey={syncKey}/>
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
                                <UsersSearchBar users={virtualUsers} setCurrentUsers={setCurrentVirtualUsers}/>
                                <div className="flex flex-col gap-2">
                                    {currentVirtualUsers?.map((u) => (
                                        <UserCard key={u.role + u.publicId + "-virtual"} user={u} onUpdateAction={virtualRefetch}
                                                  syncKey={virtualSyncKey}/>
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