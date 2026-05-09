import UserPanelPageClient from "@/app/admin/user/user-panel-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Panel Administrateur",
};
export default function UserPanelPage(): ReactElement {
    return <UserPanelPageClient />;
}
