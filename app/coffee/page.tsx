"use client";

import { ReactElement, useEffect } from "react";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";

export default function CoffeePage(): ReactElement {
    useEffect(() => {
        goralysFetchClient("BREW", "coffee").then();
    }, []);

    return <></>;
}
