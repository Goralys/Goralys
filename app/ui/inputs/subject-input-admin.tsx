import { clsx } from "clsx";
import { Subject } from "@/app/lib/types";
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
    helper =
        subjectData.status === "submitted"
            ? "Cette question est en attente de validation."
            : subjectData.status === "not_submitted"
              ? "Cette question n'a pas encore été envoyée."
              : subjectData.status === "rejected"
                ? "Cette question n'a pas été validée par le professeur."
                : subjectData.status === "approved"
                  ? "Cette question a été validée."
                  : "";

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
