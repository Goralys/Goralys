"use client";

import Link from "next/link";
import Cookies from "universal-cookie";
import { ReactElement, useEffect, useState } from "react";
import { CookieIcon } from "@sidekickicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/outline";

export default function CookieBanner(): ReactElement | null {
    const cookies = new Cookies();
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const cookies = new Cookies();
        if (!cookies.get("cookie-banner-dismissed")) {
            // eslint-disable-next-line react-hooks/set-state-in-effect
            setVisible(true);
        }
    }, []);

    if (!visible) return null;

    const discard = (): void => {
        cookies.set("cookie-banner-dismissed", "1", {
            path: "/",
            expires: new Date(Date.now() + 1000 * 60 * 60 * 24 * 365),
        });
        setVisible(false);
    };

    return (
        <div className="fixed bottom-4 left-1/2 -translate-x-1/2 w-fit max-w-md z-10 bg-sky-200 rounded-md shadow-lg p-4 flex flex-col gap-2">
            <div className="flex items-center justify-between">
                <p className="font-semibold flex items-center gap-1">
                    <CookieIcon className="size-5" />
                    Cookies
                </p>
                <button
                    onClick={discard}
                    className="cursor-pointer hover:bg-sky-300 hover:shadow-md hover:-translate-y-1 rounded-md p-0.5
                    transition-all ease-out duration-500"
                    title="Fermer"
                >
                    <XMarkIcon className="size-4" />
                </button>
            </div>
            <p className="text-sm">
                Goralys utilise uniquement des cookies fonctionnels. Pour plus d&apos;informations, consultez{" "}
                <Link href="/cookies" className="text-sky-600 underline hover:text-sky-800 transition-colors duration-200">
                    cette page
                </Link>
            </p>
        </div>
    );
}
