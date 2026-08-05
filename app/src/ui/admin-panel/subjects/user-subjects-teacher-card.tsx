import { Subject } from "@goralys/core";
import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";
import SubjectTeachersDonut from "@/app/src/ui/admin-panel/subjects/subject-teachers-donut";
import StatusLegend from "@/app/src/ui/admin-panel/subjects/status-legend";
import CardTitle from "@/app/src/ui/card-title";

interface Props {
    subjects: Subject[] | null;
}

export default function UserSubjectsTeacherCard({ subjects }: Props): ReactElement {
    if (!subjects) return <></>;

    const total = subjects.length;
    const notSubmitted = subjects.filter((s) => s.status === "not_submitted").length;
    const submitted = subjects.filter((s) => s.status === "submitted").length;
    const rejected = subjects.filter((s) => s.status === "rejected").length;
    const approved = subjects.filter((s) => s.status === "approved").length;

    return (
        <Card className="flex flex-col grow bg-sky-200 m-2">
            <CardTitle title="Sujets" />
            <Card className="flex flex-row w-full" key={subjects.length}>
                <SubjectTeachersDonut
                    total={total}
                    approved={approved}
                    rejected={rejected}
                    submitted={submitted}
                    notSubmitted={notSubmitted}
                />
                <span className="basis-1/6 grow-0 shrink min-w-0" />
                <StatusLegend total={total} approved={approved} rejected={rejected} submitted={submitted} notSubmitted={notSubmitted} />
            </Card>
        </Card>
    );
}
