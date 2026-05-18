import { clsx } from "clsx";
import { getStatusHelper, Subject } from "@/app/lib/types";
import { ArrowDownTrayIcon } from "@heroicons/react/24/outline";
import { SubjectTextArea } from "@/app/ui/inputs/subject-text-area";
import Checkbox from "@/app/ui/inputs/checkbox";
import React, { ChangeEventHandler, ReactElement, useState } from "react";
import { buildApiUrl } from "@/app/lib/fetch/fetch.client";

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
    const [currentValue, setCurrentValue] = useState(initialValue);
    const MAX_CHARS = 250;

    const handleOnChange = (e: React.ChangeEvent<HTMLTextAreaElement>): void => {
        if (e.target.value.length > MAX_CHARS) {
            return;
        }
        setCurrentValue(e.target.value);
        if (onChangeAction) {
            onChangeAction(e);
        }
    };

    const getDraft = (): void => {
        window.location.href = buildApiUrl("subjects/draft", {
            teacher: subjectData.teacherToken,
            student: subjectData.studentToken,
            topic: subjectData.topic,
            "file-name": `Brouillon - ${subjectData.student} ${subjectData.topic}`,
        });
    };

    return (
        <div
            className={clsx("relative mt-3 group min-w-50", {
                "mb-5": helper !== undefined,
                "mb-1": helper === undefined,
            })}
        >
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
                <div className="flex flex-col">
                    <p className="mt-0 mb-0 p-0 relative text-[11px] italic text-gray-600">{currentValue.length}/250 caractères</p>
                    {helper.length !== 0 && <p className="mt-0 self-center relative text-[13px] italic text-gray-600">*{helper}</p>}
                </div>

                <Checkbox
                    id={`interdisciplinary-teacher-${subjectData.studentToken}-${subjectData.teacherToken}`}
                    className="ml-auto self-center"
                    label="Question transversale"
                    setValueAction={() => {}}
                    defaultValue={subjectData.interdisciplinary}
                    disabled
                />
            </div>
        </div>
    );
}
