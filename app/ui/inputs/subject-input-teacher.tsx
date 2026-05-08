import {clsx} from "clsx";
import {SubjectInputMultilineProps} from "@/app/lib/types";
import { ArrowDownTrayIcon } from "@heroicons/react/24/outline";
import {SubjectTextArea} from "@/app/ui/inputs/subject-text-area";
import Checkbox from "@/app/ui/inputs/checkbox";
import React, {useState} from "react";

export function SubjectInputTeacher({ id, label, helper, subjectData, onChangeAction}: SubjectInputMultilineProps) {
    const requestUrl = `${process.env.NEXT_PUBLIC_API_DOMAIN}/subjects/draft`
    helper = subjectData.status === "submitted"
        ? "Cette question est en attente de validation."
        : subjectData.status === "not_submitted" ? "Cette question n'a pas encore été envoyée."
        : subjectData.status === "rejected" ? "Vous n'avez pas validé cette question, l'élève doit en envoyer une nouvelle."
        : subjectData.status === "approved" ? "Vous avez validé cette question, elle ne peut plus être modifiée." : ""

    const initialValue = subjectData.status == "rejected" ? (subjectData.lastRejected ?? "") : (subjectData.subject ?? "");
    const [currentValue, setCurrentValue] = useState(initialValue);
    const MAX_CHARS = 250

    const handleOnChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
        if (e.target.value.length > MAX_CHARS) {
            return;
        }
        setCurrentValue(e.target.value);
        if (onChangeAction) {
            onChangeAction(e);
        }
    };

    return (
        <div className={clsx(
            "relative mt-3 group min-w-50",
            {
                "mb-5": helper !== undefined,
                "mb-1": helper === undefined
            },
        )}
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
                {subjectData.hasDraft &&
                    <form action={requestUrl} method="POST">
                        <input type="hidden" name="teacher-token" value={subjectData.teacherToken} />
                        <input type="hidden" name="student-token" value={subjectData.studentToken} />
                        <input type="hidden" name="topic" value={subjectData.topic} />
                        <input type="hidden" name="file-name" value={`Brouillon - ${subjectData.student} ${subjectData.topic}`} />
                        <button className="h-6 w-6 cursor-pointer bg-sky-200 rounded-md items-center justify-center
                        hover:bg-sky-300 hover:shadow-md hover:-translate-y-1 transition-all ease-out duration-500"
                                type="submit"
                                title="Télécharger le brouillon de l'élève">
                            <ArrowDownTrayIcon className="size-5 m-auto" />
                        </button>
                    </form>
                }
            </div>

            <div className="flex flex-row content-between w-full">
                <div className="flex flex-col">
                    <p className="mt-0 mb-0 p-0 relative text-[11px] italic text-gray-600">
                        {currentValue.length}/250 caractères
                    </p>
                    {helper.length !== 0 && (
                        <p className="mt-0 self-center relative text-[13px] italic text-gray-600">
                            *{helper}
                        </p>
                    )}
                </div>

                <Checkbox id={`interdisciplinary-teacher-${subjectData.studentToken}-${subjectData.teacherToken}`}
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
