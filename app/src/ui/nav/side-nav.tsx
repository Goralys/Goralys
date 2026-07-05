"use client";

import { NavLink } from "@/app/src/ui/nav/nav-link";
import { UserNav } from "@/app/src/ui/nav/user-nav";
import { ReactElement, useEffect, useState } from "react";
import { buildArray, cookiesGet, cookiesOnChange, ROLE_KEY, USER_ROLES, UserRole } from "@goralys/core";
import Image from "next/image";

export function SideNav(): ReactElement {
    const [role, setRole] = useState<UserRole["role"]>("none");

    useEffect(() => {
        const run = (): void => {
            const current: string | boolean | number = cookiesGet(ROLE_KEY) ?? "none";
            setRole(USER_ROLES.includes(current as UserRole["role"]) ? (current as UserRole["role"]) : "none");
        };

        const onChange = (): void => {
            const role: string | boolean | number = cookiesGet(ROLE_KEY) ?? "none";
            setRole(USER_ROLES.includes(role as UserRole["role"]) ? (role as UserRole["role"]) : "none");
        };

        run();
        return cookiesOnChange(onChange);
    }, []);

    function getSubjectLinkText(): string {
        switch (role) {
            case "student":
                return "Mes Questions";
            case "teacher":
                return "Mes Élèves";
            case "admin":
                return "Questions";
            case "none":
                return "Mon Espace";
        }
    }

    const links: { name: string; url: string }[] = buildArray(
        { name: "Accueil", url: "/" },
        { name: getSubjectLinkText(), url: "/subject" },
        role == "admin" && { name: "Utilisateurs", url: "/admin/user" },
        role == "admin" && { name: "Accès", url: "/admin/admin" },
        role == "admin" && { name: "Support", url: "/support" },
    );

    return (
        <div className="min-w-50 w-55 h-auto min-h-screen fixed top-0 flex flex-col m-0 p-2 rounded-xl">
            <div className="flex rounded-md min-w-full h-25 bg-sky-500 mb-2 p-4">
                <Image
                    src="/logo/goralys-logo.svg"
                    width={125}
                    height={10}
                    alt="Goralys logo"
                    loading="eager"
                    priority
                    className="-ml-3 self-center w-auto h-auto"
                />
            </div>
            <div className="flex flex-col gap-2">
                {links.map((link) => (
                    <NavLink key={link.url} name={link.name} url={link.url} />
                ))}
            </div>
            <div className="grow bg-gray-100 rounded-xl my-3"></div>
            <NavLink key="/help" name="Centre d'Aide" url="/help" />
            <UserNav />
        </div>
    );
}
