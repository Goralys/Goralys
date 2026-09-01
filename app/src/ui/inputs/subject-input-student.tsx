"use client";

import React, { ChangeEventHandler, ReactElement } from "react";
import { getStatusHelper, Subject } from "@goralys/core";
import { SubjectTextArea } from "@/app/src/ui/inputs/subject-text-area";
import Checkbox from "@/app/src/ui/inputs/checkbox";

interface Props {
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
}: Props): ReactElement {
    const initialValue = subjectData.status == "rejected" ? (subjectData.lastRejected ?? "") : (subjectData.subject ?? "");
    const MAX_CHARS = 250;
    helper = getStatusHelper(subjectData.status, "student");

    const editable = subjectData.status != "approved" && subjectData.status != "submitted";

    const handleOnChange = (e: React.ChangeEvent<HTMLTextAreaElement>): void => {
        if (onChangeAction) {
            onChangeAction(e);
        }
    };

    return (
        <div className="relative mt-2 group min-w-50 mb-0 flex sm:block flex-col">
            <SubjectTextArea
                id={id}
                disabled={!editable}
                defaultValue={initialValue}
                maxLength={MAX_CHARS}
                onChangeAction={handleOnChange}
                label={label}
                subjectData={subjectData}
                animate={editable}
                helper={helper}
            />

            <div className="flex flex-row content-between w-full">
                <Checkbox
                    className="m-0 sm:-mt-7.5 sm:ml-auto self-center"
                    label="Question transversale"
                    setValueAction={setIsInterdisciplinaryAction}
                    defaultValue={subjectData.interdisciplinary}
                    disabled={!editable}
                />
            </div>
        </div>
    );
}
