import { ReactElement } from "react";
import AdminExportPageClient from "@/app/admin/export/admin-export-page-client";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Panel Administrateur",
};

export default function AdminExportPage(): ReactElement {
    return <AdminExportPageClient />;
}
