import SubjectAdminPageClient from "@/app/subject/admin/subject-admin-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Questions",
};
export default function AdminPage(): ReactElement {
    return <SubjectAdminPageClient />;
}
