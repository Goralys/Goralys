"use client";

import { useEffect } from "react";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";

export default function TeaPage(): void {
    useEffect(() => {
        goralysFetchClient("tea", {
            method: "POST",
            headers: {
                "X-HTTP-Method-Override": "BREW",
            },
        }).then();
    }, []);
}
