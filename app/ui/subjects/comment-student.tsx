import { Subject } from "@/app/src/lib/types";
import { TextArea } from "@/app/ui/inputs/text-area";
import { ChangeEventHandler, ReactElement } from "react";
import { SubjectTextArea } from "@/app/ui/inputs/subject-text-area";
import { ChevronRightIcon } from "@heroicons/react/24/outline";

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
                    <ChevronRightIcon className="w-5 h-5 transition-transform duration-200 group-open:rotate-90" />
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
