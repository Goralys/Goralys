"use client";

import { getStatusLabel, Subject } from "@goralys/core";
import React, { ReactElement } from "react";
import { SubjectInputAdmin } from "@/app/src/ui/inputs/subject-input-admin";

interface Props {
    subjectData: Subject;
}

export default function ReadonlyCard({ subjectData }: Props): ReactElement {
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
            <div className="flex flex-row justify-between">
                <p>Statut de la question: {getStatusLabel(subjectData.status)}</p>
            </div>
        </div>
    );
}
