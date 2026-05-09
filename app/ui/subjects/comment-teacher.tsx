import { Subject } from "@/app/lib/types";
import { TextArea } from "@/app/ui/inputs/text-area";
import { ChangeEventHandler, ReactElement, RefObject } from "react";

interface CommentTeacherProps {
    subjectData: Subject;
    disabled: boolean;
    ref?: RefObject<HTMLTextAreaElement | null>;
    onChange?: ChangeEventHandler<HTMLTextAreaElement>;
}

export default function CommentTeacher({ subjectData, disabled, ref, onChange }: CommentTeacherProps): ReactElement {
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
                    <svg
                        className="w-5 h-5 text-gray-900 transition group-open:rotate-90"
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        fill="currentColor"
                        viewBox="0 0 16 16"
                    >
                        <path
                            fillRule="evenodd"
                            d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"
                        ></path>
                    </svg>
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
