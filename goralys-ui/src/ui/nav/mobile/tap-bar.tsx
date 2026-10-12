import { ComponentType, ReactElement, SVGProps, useEffect, useState } from "react";
import { buildArray, cookiesGet, cookiesOnChange, ROLE_KEY, USER_ROLES, UserRole } from "@goralys/core";
import { AcademicCapIcon, HomeIcon } from "@heroicons/react/24/outline";
import { TapBarLink } from "#lib/ui/nav/mobile/tap-bar-link";
import { TapBarUser } from "#lib/ui/nav/mobile/tap-bar-user";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

type Link = {
    name: string;
    url: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
};

export function TapBar(): ReactElement {
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

    const links: Link[] = buildArray(!useGoralysUI().isMobile && { name: "Accueil", url: "/", icon: HomeIcon }, {
        name: getSubjectLinkText(),
        url: "/subject",
        icon: AcademicCapIcon,
    });

    return (
        <div className="z-50 fixed bottom-0 w-full h-12 bg-white flex flex-row content-center justify-evenly">
            {links.map((l) => (
                <TapBarLink key={l.url} name={l.name} url={l.url} icon={l.icon} />
            ))}
            <TapBarUser />
        </div>
    );
}
