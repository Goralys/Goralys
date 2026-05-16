import { clsx } from "clsx";
import { getStatusHelper, Subject } from "@/app/lib/types";
import { SubjectTextArea } from "@/app/ui/inputs/subject-text-area";
import { ChangeEventHandler, ReactElement } from "react";

interface SubjectInputMultilineProps {
    helper?: string;
    id: string;
    label: string;
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>;
    setIsInterdisciplinaryAction?: (v: boolean) => void;
    subjectData: Subject;
}

export function SubjectInputAdmin({ id, label, helper, subjectData, onChangeAction }: SubjectInputMultilineProps): ReactElement {
    helper = getStatusHelper(subjectData.status, "admin");

    return (
        <div
            className={clsx("relative mt-3 group min-w-50", {
                "mb-5": helper !== undefined,
                "mb-1": helper === undefined,
            })}
        >
            <SubjectTextArea
                id={id}
                disabled={true}
                onChangeAction={onChangeAction}
                label={label}
                animate={false}
                subjectData={subjectData}
                defaultValue={subjectData.subject}
            />

            {helper.length !== 0 && <p className="mt-0 absolute text-[13px] italic text-gray-600">*{helper}</p>}
        </div>
    );
}
