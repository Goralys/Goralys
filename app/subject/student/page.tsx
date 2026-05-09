import SubjectStudentPageClient from "@/app/subject/student/subject-student-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Questions",
};
export default function StudentPage(): ReactElement {
    return <SubjectStudentPageClient />;
}
