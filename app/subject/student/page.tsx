'use client';

import StudentCard from "@/app/ui/subjects/student-card";
import {useSubjects} from "@/app/hooks/useSubjects";
import Cookies from "universal-cookie";
import { Suspense } from "react";
import StudentCardSkeleton from "@/app/ui/skeletons/subjects/student-card";

export default function Page() {
    const {subjects, refetch, syncKey} = useSubjects("student");
    const cookies = new Cookies();
    const updateSubjects = async () => {
        cookies.set(syncKey, "0", { path: '/' });
        await refetch();
    }

    const skeletons = Array.from({ length: 3 }, (_, i) => <StudentCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2">
                <p className="underline text-2xl self-start mb-3">Vos questions :</p>
                <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                    <div className="flex flex-col gap-2">
                        {subjects === null
                            ? <>
                                skeletons
                            </>
                            :
                            subjects.map((s) => (
                                <StudentCard
                                    key={s.studentToken + s.teacherToken + s.topic}
                                    subjectData={s}
                                    onUpdateAction={updateSubjects}
                                />
                            ))
                        }
                    </div>
                </Suspense>
            </div>
        </div>
    );
}
