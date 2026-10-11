"use client";

import { clsx } from "clsx";
import { ReactElement } from "react";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

export function NavLink({ name, url, exact = false }: { name: string; url: string; exact?: boolean }): ReactElement {
    const Link = useGoralysUI().Link;

    const current = useGoralysUI().usePathname();
    const isActive = exact || url === "/" ? current === url : current === url || current.startsWith(`${url}/`);

    return (
        <Link
            className={clsx(
                "hover:bg-sky-200 h-12.5 w-full items-center flex flex-row hover:text-sky-600 left-1 rounded-md transition-colors p-2",
                {
                    "bg-sky-200 text-sky-600": isActive,
                    "bg-gray-100 text-gray-900": !isActive,
                },
            )}
            href={url}
        >
            {name}
        </Link>
    );
}
