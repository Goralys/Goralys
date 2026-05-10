"use client";

import { Subject } from "@/app/lib/types";
import { SubjectInputAdmin } from "@/app/ui/inputs/subject-input-admin";
import { ReactElement } from "react";

export default function AdminCard({ subjectData }: { subjectData: Subject }): ReactElement {
    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <strong>{subjectData.student}</strong>
                <strong>
                    {subjectData.topic} ({subjectData.teacher})
                </strong>
            </div>
            <SubjectInputAdmin
                id={subjectData.studentToken + subjectData.teacherToken + "-input"}
                subjectData={subjectData}
                label="Question de l'Elève"
            />
        </div>
    );
}
