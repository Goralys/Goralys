"use client";

import Link from "next/link";
import Cookies from "universal-cookie";
import { ReactElement, useEffect, useState } from "react";
import { usePathname } from "next/navigation";
import { onUserEvent } from "@/app/lib/auth/user-event";
import { UserCircleIcon } from "@heroicons/react/24/outline";
import clsx from "clsx";

export function UserNav(): ReactElement {
    const [text, setText] = useState<string | null>(null);
    const [loggedIn, setLoggedIn] = useState<boolean>(false);
    const current = usePathname();

    const targetUrl = loggedIn ? "/user/me" : "/user/login";
    const isActive = current === targetUrl || current.startsWith(`${targetUrl}/`);

    useEffect(() => {
        const cookies = new Cookies();

        const run = (): void => {
            const isLoggedIn = !!cookies.get("username");
            setLoggedIn(isLoggedIn);

            let name = isLoggedIn ? (cookies.get("full-name") ?? "") : "Se connecter";
            if (name.length > 20) name = name.substring(0, 19) + "...";
            setText(name);
        };

        run();
    }, []);

    useEffect(() => {
        const unsubscribe = onUserEvent((event) => {
            const cookies = new Cookies();
            const isLoggedIn = event === "login";

            if (!isLoggedIn) {
                cookies.remove("full-name");
                cookies.remove("user-role");
                cookies.remove("username");
            }

            setLoggedIn(isLoggedIn);

            let name = isLoggedIn ? (cookies.get("full-name") ?? "") : "Se connecter";
            if (name.length > 25) name = name.substring(0, 22) + "...";
            setText(name);
        });

        return (): void => {
            unsubscribe?.();
        };
    }, []);

    return (
        <Link
            className={clsx(
                "h-12.5 w-full flex items-center gap-2 rounded-md transition-colors p-1.5",
                "hover:bg-sky-200 hover:text-sky-600",
                {
                    "bg-sky-200 text-sky-600": isActive,
                    "bg-gray-100 text-gray-900": !isActive,
                },
            )}
            href={targetUrl}
        >
            {loggedIn && <UserCircleIcon width={27.5} className="-mr-1.25" />}
            {text}
        </Link>
    );
}
