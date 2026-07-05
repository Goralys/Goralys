"use client";

import { ReactElement, useEffect } from "react";
import { goralysFetchClient } from "@goralys/core";

export default function TeaPage(): ReactElement {
    useEffect(() => {
        goralysFetchClient("BREW", "tea").then();
    }, []);

    return <></>;
}
