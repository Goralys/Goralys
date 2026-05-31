"use client";

import { useImportTopicsModal } from "@/app/src/ui/modals/import-topics/import-topics-modal-provider";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Button } from "@/app/src/ui/button";
import { useSubjects } from "@/app/src/hooks/useSubjects";
import AdminCard from "@/app/src/ui/subjects/admin-card";
import { Subject } from "@/app/src/lib/types";
import { SubjectsSearchBar } from "@/app/src/ui/subjects/subjects-search-bar";
import { ReactElement, Suspense, useState } from "react";
import AdminSubjectCardSkeleton from "@/app/src/ui/skeletons/subjects/admin-card";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";
import Cookies from "universal-cookie";

export default function SubjectAdminPageClient(): ReactElement {
    const modal = useImportTopicsModal();
    const confirm = useConfirm();
    const toast = useToast();
    const { subjects, refetch, syncKey } = useSubjects("admin");
    const [currentSubjects, setCurrentSubjects] = useState<Subject[] | null>(subjects);
    const cookies = new Cookies();

    const sendTopics = async (): Promise<void> => {
        const csrfToken = await fetchCsrfClient("import-topics");
        const file = await modal.showImportTopicsModal();

        if (file === "modalClosed") return;

        if (!file) {
            toast.showToast({
                type: "warning",
                title: "Import des données",
                message: "Veuillez importer un fichier.",
            });
            return;
        }

        const formData = new FormData();
        formData.append("csrf-token", csrfToken ?? "");
        formData.append("topics-file", file);

        const res = await goralysFetchClient("POST", "topics/import", formData);

        if (res.ok) {
            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "utilisateurs.txt";
            a.click();
            URL.revokeObjectURL(url);
            cookies.set(syncKey, "0", { path: "/" });
            cookies.set("users-synced", "0", { path: "/" });
            cookies.set("virtual-users-synced", "0", { path: "/" });
            await refetch();
            setCurrentSubjects(subjects || []);
            return;
        }

        await handleToastRequest(res, toast.showToast, false);
    };

    const deleteTopics = async (): Promise<void> => {
        const confirmResult = await confirm.showConfirm({
            title: "Suppression des sujets",
            message: "Ête-vous sûr de vouloir supprimer les sujets et les utilisateurs (sauf administrateurs) ?",
        });

        if (!confirmResult) return;

        const res = await goralysFetchClient("DELETE", buildApiUrl("topics", { "csrf-token": await fetchCsrfClient("delete-topics") }));

        await handleToastRequest(res, toast.showToast, false);

        if (res.ok) {
            cookies.set(syncKey, "0", { path: "/" });
            await refetch();
            setCurrentSubjects(subjects || []);
        }
    };

    const exportSubjects = async (): Promise<void> => {
        const csrfToken = await fetchCsrfClient("export-subjects");
        const payload = {
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("POST", "subjects/export", payload);

        if (res.ok) {
            const blob = await res.blob();

            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "sujets-go.zip";
            a.click();

            URL.revokeObjectURL(url);
            return;
        }

        await handleToastRequest(res, toast.showToast, false);
    };

    const skeletons = Array.from({ length: 3 }, (_, i) => <AdminSubjectCardSkeleton key={i} />);

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2 bg-sky-200 rounded-md">
                <p className="underline text-xl self-start mb-2.5">Gestion des sujets:</p>
                <div className="w-150">
                    <Button text="Importer les sujets" type="button" onClick={sendTopics} />
                    <Button text="Exporter les sujets en PDF" type="button" onClick={exportSubjects} />
                    <Button text="Supprimer les sujets" type="button" onClick={deleteTopics} color="red" />
                </div>
            </div>
            <div className="h-auto w-fit p-2 mt-4">
                <p className="underline w-200 text-2xl self-start mb-3">Les questions de l&apos;établissement :</p>
                <Suspense fallback={<div className="flex flex-col gap-2 items-center w-full">{skeletons}</div>}>
                    {subjects === null ? (
                        <div className="flex flex-col gap-2">{skeletons}</div>
                    ) : (
                        <>
                            <SubjectsSearchBar subjects={subjects} setCurrentSubjects={setCurrentSubjects} />
                            <div className="flex flex-col gap-2 items-center w-full">
                                {currentSubjects?.map((s) => (
                                    <AdminCard
                                        key={s.studentToken + s.teacherToken}
                                        subjectData={s}
                                        syncKey="subjects-synced-admin"
                                        onUpdateAction={refetch}
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
