"use client";

import { ReactElement, useEffect, useState } from "react";
import { cookiesGet, emptyUserCacheClient, onUserEvent, USERNAME_KEY } from "@goralys/core";
import { UserCircleIcon } from "@heroicons/react/24/outline";
import clsx from "clsx";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

export function TapBarUser(): ReactElement {
    const Link = useGoralysUI().Link;

    const [text, setText] = useState<string | null>(null);
    const [loggedIn, setLoggedIn] = useState<boolean>(false);
    const current = useGoralysUI().usePathname();

    const targetUrl = loggedIn ? "/user/me" : "/user/login";
    const isActive = current === targetUrl || current.startsWith(`${targetUrl}/`);

    useEffect(() => {
        const run = (): void => {
            const isLoggedIn = !!cookiesGet(USERNAME_KEY);
            setLoggedIn(isLoggedIn);
            setText(isLoggedIn ? "Mon compte" : "Se connecter");
        };

        run();
    }, []);

    useEffect(() => {
        const unsubscribe = onUserEvent((event) => {
            const isLoggedIn = event === "login";

            if (!isLoggedIn) {
                emptyUserCacheClient();
            }

            setLoggedIn(isLoggedIn);
            setText(isLoggedIn ? "Mon compte" : "Se connecter");
        });

        return (): void => {
            unsubscribe?.();
        };
    }, []);

    return (
        <Link className={"flex flex-col w-20 items-center"} href={targetUrl}>
            <UserCircleIcon className="w-6 h-6 relative top-1" />
            <p className="text-sm">{text}</p>
            <span
                className={clsx("relative bottom-0 w-full h-0.5", {
                    "bg-sky-400": isActive,
                    "bg-sky-200": !isActive,
                })}
            />
        </Link>
    );
}
