import { getStatusHelper, Subject } from "@/app/src/lib/types";
import { ArrowDownTrayIcon } from "@heroicons/react/24/outline";
import { SubjectTextArea } from "@/app/src/ui/inputs/subject-text-area";
import Checkbox from "@/app/src/ui/inputs/checkbox";
import React, { ChangeEventHandler, ReactElement } from "react";
import { buildApiUrl } from "@/app/src/lib/fetch/fetch.client";

interface SubjectInputMultilineProps {
    helper?: string;
    id: string;
    label: string;
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>;
    setIsInterdisciplinaryAction?: (v: boolean) => void;
    subjectData: Subject;
}

export function SubjectInputTeacher({ id, label, helper, subjectData, onChangeAction }: SubjectInputMultilineProps): ReactElement {
    helper = getStatusHelper(subjectData.status, "teacher");

    const initialValue = subjectData.status == "rejected" ? (subjectData.lastRejected ?? "") : (subjectData.subject ?? "");
    const MAX_CHARS = 250;

    const handleOnChange = (e: React.ChangeEvent<HTMLTextAreaElement>): void => {
        if (onChangeAction) {
            onChangeAction(e);
        }
    };

    const getDraft = (): void => {
        window.location.href = buildApiUrl(
            "subjects/draft",
            {
                teacher: subjectData.teacherToken,
                student: subjectData.studentToken,
                topic: subjectData.topic,
                "file-name": `Brouillon - ${subjectData.student} ${subjectData.topic}`,
            },
            true,
        );
    };

    return (
        <div className="relative mt-2 group min-w-50 mb-0">
            <div className="flex flex-row">
                <SubjectTextArea
                    id={id}
                    disabled={true}
                    defaultValue={initialValue}
                    maxLength={MAX_CHARS}
                    onChangeAction={handleOnChange}
                    label={label}
                    subjectData={subjectData}
                    animate={false}
                    helper={helper}
                />
                {subjectData.hasDraft && (
                    <button
                        className="h-6 w-6 cursor-pointer bg-sky-200 rounded-md items-center justify-center
                        hover:bg-sky-300 hover:shadow-md hover:-translate-y-1 transition-all ease-out duration-500"
                        type="submit"
                        title="Télécharger le brouillon de l'élève"
                        onClick={getDraft}
                    >
                        <ArrowDownTrayIcon className="size-5 m-auto" />
                    </button>
                )}
            </div>

            <div className="flex flex-row content-between w-full">
                <Checkbox
                    id={`interdisciplinary-teacher-${subjectData.studentToken}-${subjectData.teacherToken}`}
                    className="ml-auto -mt-7.5 mb-2.5 self-center"
                    label="Question transversale"
                    setValueAction={() => {}}
                    defaultValue={subjectData.interdisciplinary}
                    disabled
                />
            </div>
        </div>
    );
}
