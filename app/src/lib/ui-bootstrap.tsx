"use client";

import { ReactElement, ReactNode } from "react";
import { GoralysUIConfig, GoralysUIProvider } from "@goralys/ui";
import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter, useSearchParams } from "next/navigation";

const nextUIConfig: GoralysUIConfig = {
    Link,
    Image,
    useRouter,
    useSearchParams,
    usePathname,
    isMobile: false,
};

export default function UIBootstrap({ children }: { children: ReactNode }): ReactElement {
    return <GoralysUIProvider config={nextUIConfig}>{children}</GoralysUIProvider>;
}
