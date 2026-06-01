/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { cacheUserDataClient } from "@/app/src/lib/user/user.client";
import { emitUserEvent } from "@/app/src/lib/auth/user-event";

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
