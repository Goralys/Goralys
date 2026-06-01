"use client";

import StudentCard from "@/app/src/ui/subjects/student-card";
import { useSubjects } from "@/app/src/hooks/useSubjects";
import Cookies from "universal-cookie";
import { ReactElement, Suspense } from "react";
import StudentCardSkeleton from "@/app/src/ui/skeletons/subjects/student-card";

export default function SubjectStudentPageClient(): ReactElement {
    const { subjects, refetch, syncKey } = useSubjects("student");
    const cookies = new Cookies();
    const updateSubjects = async (): Promise<void> => {
        cookies.set(syncKey, "0", { path: "/" });
        await refetch();
    };

    const skeletons = Array.from({ length: 2 }, (_, i) => <StudentCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2">
                <p className="underline text-2xl self-start mb-3">Vos questions :</p>
                <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                    <div className="flex flex-col gap-2">
                        {subjects === null ? (
                            <>{skeletons}</>
                        ) : (
                            subjects.map((s) => (
                                <StudentCard
                                    key={s.studentToken + s.teacherToken + s.topic}
                                    subjectData={s}
                                    onUpdateAction={updateSubjects}
                                />
                            ))
                        )}
                    </div>
                </Suspense>
            </div>
        </div>
    );
}
