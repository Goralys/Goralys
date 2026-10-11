"use client";

import { User } from "@goralys/core";
import { Card } from "#lib/ui/card";
import { Button } from "#lib/ui/button";
import { AcademicCapIcon, BookOpenIcon } from "@heroicons/react/24/outline";
import { ReactElement } from "react";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

interface Props {
    user: User;
}

export function UserCard({ user }: Props): ReactElement {
    const router = useGoralysUI().useRouter();

    return (
        <Card className="flex-col w-200! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    {
                        // No admins here.
                        user.role == "teacher" ? (
                            <BookOpenIcon width={27.5} className="mr-1.5" />
                        ) : (
                            <AcademicCapIcon width={27.5} className="mr-1.5" />
                        )
                    }
                    <strong>
                        {user.fullName.length > 25 ? user.fullName.substring(0, 24) + "..." : user.fullName} ({user.username})
                    </strong>
                </div>
                <div className="flex flex-row w-100 gap-1 place-content-end">
                    <Button
                        type="button"
                        className="w-50!"
                        text="Gérer le compte"
                        onClick={() => router.push("/admin/user?u=" + user.publicId)}
                    />
                </div>
            </div>
        </Card>
    );
}
