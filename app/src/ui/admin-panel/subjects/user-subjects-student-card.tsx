import { getShortFromLong, getStatusLabel, Subject, SubjectStatus } from "@goralys/core";
import { ReactElement } from "react";
import SubjectStudentsDonut from "@/app/src/ui/admin-panel/subjects/subject-students-donut";
import { Card } from "@/app/src/ui/card";

interface Props {
    subjects: Subject[] | null;
}

export default function UserSubjectsStudentCard({ subjects }: Props): ReactElement {
    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            {subjects?.map((s) => {
                const realStatus: SubjectStatus | "empty" = s.subject.trim() === "" && s.status === "not_submitted" ? "empty" : s.status;

                return (
                    <Card className="flex flex-row w-full justify-between" key={s.teacherToken}>
                        <div className="flex flex-col">
                            <p className="text-lg font-bold">{getShortFromLong(s.topic)}</p>
                            <p className="italic">Statut: {getStatusLabel(s.status)}</p>
                        </div>
                        <SubjectStudentsDonut status={realStatus} />
                    </Card>
                );
            })}
        </Card>
    );
}
