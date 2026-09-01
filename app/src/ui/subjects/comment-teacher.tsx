import { Subject } from "@goralys/core";
import { TextArea } from "@/app/src/ui/inputs/text-area";
import { ChangeEventHandler, ReactElement, RefObject } from "react";
import { ChevronRightIcon } from "@heroicons/react/24/outline";

interface Props {
    subjectData: Subject;
    disabled: boolean;
    ref?: RefObject<HTMLTextAreaElement | null>;
    onChange?: ChangeEventHandler<HTMLTextAreaElement>;
}

export default function CommentTeacher({ subjectData, disabled, ref, onChange }: Props): ReactElement {
    const visible = subjectData.status === "submitted";

    if (!visible) {
        return <></>;
    }

    return (
        <>
            <details
                key={`comment-teacher-details-for-${subjectData.student}-${subjectData.topic}`}
                className="group"
                open={subjectData.status === "rejected"}
            >
                <summary className="flex flex-row cursor-pointer">
                    <ChevronRightIcon className="w-5 h-5 transition-transform duration-200 group-open:rotate-90" />
                    <span>Votre commentaire</span>
                </summary>

                <TextArea
                    key={`comment-teacher-details-for-${subjectData.student}-${subjectData.topic}`}
                    id={subjectData.studentToken + subjectData.teacherToken + "-subject-comment"}
                    label="Commentaire"
                    defaultValue={subjectData.comment}
                    onChangeAction={onChange}
                    disabled={disabled}
                    ref={ref}
                />
            </details>
        </>
    );
}
