import UsersPanelPageClient from "@/app/admin/users/users-panel-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Panel Administrateur",
};
export default function UsersPanelPage(): ReactElement {
    return <UsersPanelPageClient />;
}
