'use client';

import {useEffect} from "react";
import {goralysFetchClient} from "@/app/lib/fetch/fetch.client";

export default function Page() {
    useEffect(() => {
        goralysFetchClient('coffee', {
            method: "POST",
            headers: {
                'X-HTTP-Method-Override': 'BREW',
            }
        }).then();
    }, []);
}