import SubjectStudentPageClient from "@/app/subject/student/subject-student-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Questions",
};
export default function StudentPage(): ReactElement {
    return <SubjectStudentPageClient />;
}
