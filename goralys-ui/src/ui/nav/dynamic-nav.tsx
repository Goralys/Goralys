"use client";

import { ReactElement } from "react";
import { useMediaQuery } from "#lib/hooks/use-media-query";
import { SideNav } from "#lib/ui/nav/desktop/side-nav";
import { TapBar } from "#lib/ui/nav/mobile/tap-bar";

export function DynamicNav(): ReactElement {
    const isDesktop = useMediaQuery("(min-width: 640px)"); // sm tailwind = 640px

    return isDesktop ? <SideNav /> : <TapBar />;
}
