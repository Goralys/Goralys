import { FloatingInput } from "@/app/ui/inputs/floating-input";
import React, { FormEvent, ReactElement, useEffect, useState } from "react";
import { User } from "@/app/lib/types";

type UserRoleFilter = "all" | "student" | "teacher";

const roleFields: Record<UserRoleFilter, string> = {
    all: "Tous",
    student: "Élèves",
    teacher: "Professeurs",
};

interface UsersSearchBarProps {
    type: "real" | "virtual";
    users: User[] | null;
    setCurrentUsers: React.Dispatch<React.SetStateAction<User[] | null>>;
}

export function UsersSearchBar({ type, users, setCurrentUsers }: UsersSearchBarProps): ReactElement {
    const [currentRole, setCurrentRole] = useState<UserRoleFilter>("all");
    const [searchText, setSearchText] = useState("");

    const handleSearch = (e: FormEvent<HTMLInputElement>): void => {
        setSearchText(e.currentTarget.value);
    };

    const sortUsers = (list: User[]): User[] => {
        return [...list].sort((a, b) => {
            if (a.role !== b.role) return a.role === "teacher" ? -1 : 1; // role as a primary key
            return a.fullName.trim().toLowerCase().localeCompare(b.fullName.trim().toLowerCase(), "fr");
        });
    };

    useEffect(() => {
        if (!users) return;

        const search = searchText.trim().toLowerCase();

        const sorted = sortUsers(
            users.filter((u: User) => {
                const matchesRole = currentRole === "all" || u.role === currentRole;
                const matchesName = !search || u.fullName.trim().toLowerCase().includes(search);
                return matchesRole && matchesName;
            }),
        );

        setCurrentUsers((prev) => {
            if (JSON.stringify(prev) === JSON.stringify(sorted)) return prev;
            return sorted;
        });
    }, [searchText, currentRole, users, setCurrentUsers]);

    return (
        <div className="flex flex-row gap-2 items-end mb-4 w-175!">
            <FloatingInput id={"users-search-" + type} label="Rechercher par nom" onInput={handleSearch} />

            <div className="relative pb-0 mb-1">
                <select
                    className="border-0 border-b-2 border-sky-300 appearance-none
                    cursor-pointer outline-none focus:ring-0 text-base leading-5
                    text-heading pb-0 pr-5 subjects-search-select"
                    value={currentRole}
                    onChange={(e) => setCurrentRole(e.target.value as UserRoleFilter)}
                >
                    {Object.entries(roleFields).map(([key, label]) => (
                        <option value={key} key={key}>
                            {label}
                        </option>
                    ))}
                </select>

                <span
                    className="absolute bottom-0 left-0 w-0 h-0.5 bg-sky-500
                     transition-all duration-300 ease-in-out
                     peer-focus:w-full subjects-search-underline"
                />
            </div>
        </div>
    );
}
