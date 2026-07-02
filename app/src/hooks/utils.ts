/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Cookies from "universal-cookie";
import { useEffect } from "react";

export function useCrossTabSync(refetch: () => Promise<undefined | void>, syncKey: string): void {
    useEffect(() => {
        const cookies = new Cookies();
        const onChange = (): void => {
            if (cookies.get(syncKey) != "1") void refetch();
        };
        cookies.addChangeListener(onChange);
        return (): void => cookies.removeChangeListener(onChange);
    }, [refetch, syncKey]);
}
