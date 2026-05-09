import AdminPanelPageClient from "@/app/admin/admin/admin-panel-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Panel Administrateur",
};
export default function AdminPanelPage(): ReactElement {
    return <AdminPanelPageClient />;
}
