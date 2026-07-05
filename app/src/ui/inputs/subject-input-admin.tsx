import { getStatusHelper, Subject } from "@goralys/core";
import { SubjectTextArea } from "@/app/src/ui/inputs/subject-text-area";
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
        <div className="relative mt-2 group min-w-50">
            <SubjectTextArea
                id={id}
                disabled={true}
                onChangeAction={onChangeAction}
                label={label}
                animate={false}
                subjectData={subjectData}
                defaultValue={subjectData.subject}
                helper={helper}
            />
        </div>
    );
}
