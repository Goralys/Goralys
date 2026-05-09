"use client";

import { useEffect } from "react";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";

export default function CoffeePage(): void {
    useEffect(() => {
        goralysFetchClient("coffee", {
            method: "POST",
            headers: {
                "X-HTTP-Method-Override": "BREW",
            },
        }).then();
    }, []);
}
