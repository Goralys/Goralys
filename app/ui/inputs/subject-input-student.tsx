"use client";

import React, { useState, ChangeEventHandler, ReactElement } from "react";
import { clsx } from "clsx";
import { getStatusHelper, Subject } from "@/app/lib/types";
import { SubjectTextArea } from "@/app/ui/inputs/subject-text-area";
import Checkbox from "@/app/ui/inputs/checkbox";

interface SubjectInputMultilineProps {
    helper?: string;
    id: string;
    label: string;
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>;
    setIsInterdisciplinaryAction?: (v: boolean) => void;
    subjectData: Subject;
}

export function SubjectInputStudent({
    id,
    label,
    helper,
    subjectData,
    setIsInterdisciplinaryAction = (): void => {},
    onChangeAction,
}: SubjectInputMultilineProps): ReactElement {
    const initialValue = subjectData.status == "rejected" ? (subjectData.lastRejected ?? "") : (subjectData.subject ?? "");
    const [currentValue, setCurrentValue] = useState(initialValue);
    const MAX_CHARS = 250;

    helper = getStatusHelper(subjectData.status, "student");

    const editable = subjectData.status != "approved" && subjectData.status != "submitted";

    const handleOnChange = (e: React.ChangeEvent<HTMLTextAreaElement>): void => {
        if (e.target.value.length > MAX_CHARS) {
            return;
        }
        setCurrentValue(e.target.value);
        if (onChangeAction) {
            onChangeAction(e);
        }
    };

    return (
        <div
            className={clsx("relative mt-3 group min-w-50", {
                "mb-5": helper !== undefined,
                "mb-1": helper === undefined,
            })}
        >
            <SubjectTextArea
                id={id}
                disabled={!editable}
                defaultValue={initialValue}
                maxLength={MAX_CHARS}
                onChangeAction={handleOnChange}
                label={label}
                subjectData={subjectData}
                animate={editable}
            />

            <div className="flex flex-row content-between w-full">
                <div className="flex flex-col">
                    <p
                        className={clsx("mt-0 mb-0 p-0 relative text-[11px] italic", {
                            "text-gray-600": currentValue.length < MAX_CHARS * 0.9,
                            "text-amber-600": currentValue.length >= MAX_CHARS * 0.9 && MAX_CHARS > currentValue.length,
                            "text-red-600": currentValue.length >= MAX_CHARS,
                        })}
                    >
                        {currentValue.length}/250 caractères
                    </p>
                    {helper.length !== 0 && <p className="mt-0 self-center relative text-[13px] italic text-gray-600">*{helper}</p>}
                </div>

                <Checkbox
                    className="ml-auto self-center"
                    label="Question transversale"
                    setValueAction={setIsInterdisciplinaryAction}
                    defaultValue={subjectData.interdisciplinary}
                    disabled={!editable}
                />
            </div>
        </div>
    );
}
