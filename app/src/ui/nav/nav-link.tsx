"use client";

import Link from "next/link";
import { clsx } from "clsx";
import { usePathname } from "next/navigation";
import { ReactElement } from "react";

export function NavLink({ name, url, exact = false }: { name: string; url: string; exact?: boolean }): ReactElement {
    const current = usePathname();

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
