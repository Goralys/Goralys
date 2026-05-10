import { ReactElement } from "react";
import { Metadata } from "next";
import SubjectTeacherPageClient from "@/app/subject/teacher/subject-teacher-page-client";

export const metadata: Metadata = {
    title: "Goralys | Questions",
};
export default function TeacherPage(): ReactElement {
    return <SubjectTeacherPageClient />;
}
