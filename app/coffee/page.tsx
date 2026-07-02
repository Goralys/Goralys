"use client";

import { ReactElement, useEffect } from "react";
import { goralysFetchClient } from "@goralys/core";

export default function CoffeePage(): ReactElement {
    useEffect(() => {
        goralysFetchClient("BREW", "coffee").then();
    }, []);

    return <></>;
}
