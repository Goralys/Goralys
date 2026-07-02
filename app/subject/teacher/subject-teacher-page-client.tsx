"use client";

import { useSubjectsWeb } from "@/app/src/hooks/useSubjectsWeb";
import TeacherCard from "@/app/src/ui/subjects/teacher-card";
import { SubjectsSearchBar } from "@/app/src/ui/subjects/subjects-search-bar";
import { ReactElement, Suspense, useState } from "react";
import { Subject } from "@goralys/core";
import Cookies from "universal-cookie";
import TeacherCardSkeleton from "@/app/src/ui/skeletons/subjects/teacher-card";

export default function SubjectTeacherPageClient(): ReactElement {
    const { subjects, refetch, syncKey } = useSubjectsWeb("teacher");
    const [currentSubjects, setCurrentSubjects] = useState<Subject[] | null>(subjects || null);
    const cookies = new Cookies();
    const updateSubjects = async (): Promise<void> => {
        cookies.set(syncKey, "0", { path: "/" });
        await refetch();
    };

    const skeletons = Array.from({ length: 3 }, (_, i) => <TeacherCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2">
                <p className="underline text-2xl self-start mb-3">Les questions de vos élèves :</p>
                <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                    {subjects === null ? (
                        <div className="flex flex-col gap-2">{skeletons}</div>
                    ) : (
                        <>
                            <SubjectsSearchBar subjects={subjects} setCurrentSubjects={setCurrentSubjects} />
                            <div className="flex flex-col gap-2">
                                {currentSubjects?.map((s) => (
                                    <TeacherCard
                                        key={`card-teacher-for-${s.student}-${s.topic}`}
                                        subjectData={s}
                                        onUpdateAction={updateSubjects}
                                    />
                                ))}
                            </div>
                        </>
                    )}
                </Suspense>
            </div>
        </div>
    );
}
