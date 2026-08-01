import AdminPanelPageClient from "@/app/admin/admins/admin-panel-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Panel Administrateur",
};
export default function AdminPanelPage(): ReactElement {
    return <AdminPanelPageClient />;
}
