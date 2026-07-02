/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { cacheUserDataClient } from "@/lib/user/user.client";
import { emitUserEvent } from "@/lib/auth/user-event";

export class GoralysActionHandler {
    public handle = async (r: Response): Promise<void> => {
        const data = await r.clone().json();

        if (!data || !data.action) {
            return;
        }

        const action = data.action;
        if (action === "login-success") {
            await this.onLogin();
        }

        return;
    };

    private onLogin = async (): Promise<void> => {
        await cacheUserDataClient();
        emitUserEvent("login");
        return;
    };
}
