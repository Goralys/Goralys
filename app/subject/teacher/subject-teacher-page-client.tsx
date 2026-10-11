"use client";

import { cookiesSet, Subject, useSubjects } from "@goralys/core";
import { SubjectsFilter, SubjectsSearchBar, TeacherCard, TeacherCardSkeleton } from "@goralys/ui";
import { ReactElement, Suspense, useState } from "react";

export default function SubjectTeacherPageClient(): ReactElement {
    const { subjects, refetch, syncKey } = useSubjects("teacher");
    const [currentSubjects, setCurrentSubjects] = useState<Subject[] | null>(subjects || null);

    const updateSubjects = async (): Promise<void> => {
        cookiesSet(syncKey, "0");
        await refetch();
    };

    const skeletons = Array.from({ length: 3 }, (_, i) => <TeacherCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10 min-h-screen">
            <div className="h-auto min-w-fit p-2 sm:w-200 w-96">
                <p className="underline text-2xl self-start mb-3">Les questions de vos élèves :</p>
                <Suspense fallback={<div className="flex flex-col gap-2">{skeletons}</div>}>
                    {subjects === null ? (
                        <div className="flex flex-col gap-2">{skeletons}</div>
                    ) : (
                        <>
                            <SubjectsSearchBar subjects={subjects} setCurrentSubjects={setCurrentSubjects} />
                            <SubjectsFilter subjects={subjects} setCurrentSubjects={setCurrentSubjects} />
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
