import SubjectAdminPageClient from "@/app/subject/admin/subject-admin-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Questions",
};
export default function AdminPage(): ReactElement {
    return <SubjectAdminPageClient />;
}
