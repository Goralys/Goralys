"use client";

import { ReactElement, useEffect } from "react";
import { goralysFetchClient } from "@/app/src/lib/fetch/fetch.client";

export default function TeaPage(): ReactElement {
    useEffect(() => {
        goralysFetchClient("BREW", "tea").then();
    }, []);

    return <></>;
}
