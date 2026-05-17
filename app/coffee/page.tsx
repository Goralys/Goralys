"use client";

import { ReactElement, useEffect } from "react";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";

export default function CoffeePage(): ReactElement {
    useEffect(() => {
        goralysFetchClient("coffee", {
            method: "POST",
            headers: {
                "X-HTTP-Method-Override": "BREW",
            },
        }).then();
    }, []);

    return <></>;
}
