import { Subject } from "@/app/lib/types";
import { TextArea } from "@/app/ui/inputs/text-area";
import { ChangeEventHandler, ReactElement } from "react";
import { SubjectTextArea } from "@/app/ui/inputs/subject-text-area";

interface CommentStudentProps {
    subjectData: Subject;
    disabled: boolean;
    onChange?: ChangeEventHandler<HTMLTextAreaElement>;
}

export default function CommentStudent({ subjectData, disabled, onChange }: CommentStudentProps): ReactElement {
    const visible = !!subjectData?.comment && !(subjectData.status === "submitted" || subjectData.status === "approved");
    const showLastRejected = subjectData.status === "not_submitted" && subjectData.lastRejected;

    if (!visible) {
        return <></>;
    }

    return (
        <>
            <details
                key={`comment-student-details-for-${subjectData.teacher}-${subjectData.topic}`}
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
                    <span>Commentaire du professeur</span>
                </summary>
                {showLastRejected && (
                    <>
                        <span className="h-2 w-full block" />
                        <SubjectTextArea
                            id={subjectData.studentToken + subjectData.teacherToken + "-last-rejected"}
                            label="Votre question non validée"
                            defaultValue={subjectData.lastRejected}
                            disabled
                            animate={false}
                            subjectData={subjectData}
                        />
                    </>
                )}
                <span className="h-1.5 w-full block" />
                <TextArea
                    id={subjectData.studentToken + subjectData.teacherToken + "-subject-comment"}
                    label="Commentaire"
                    defaultValue={subjectData.comment}
                    onChangeAction={onChange}
                    disabled={disabled}
                />
            </details>
        </>
    );
}
