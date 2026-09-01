"use client";

import { ReactElement } from "react";
import { useMediaQuery } from "@/app/src/hooks/use-media-query";
import { SideNav } from "@/app/src/ui/nav/desktop/side-nav";
import TapBar from "@/app/src/ui/nav/mobile/tap-bar";

export default function DynamicNav(): ReactElement {
    const isDesktop = useMediaQuery("(min-width: 640px)"); // sm tailwind = 640px

    return isDesktop ? <SideNav /> : <TapBar />;
}
