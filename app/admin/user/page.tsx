import UserPanelPageClient from "@/app/admin/user/user-panel-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Panel Administrateur",
};
export default function UserPanelPage(): ReactElement {
    return <UserPanelPageClient />;
}
