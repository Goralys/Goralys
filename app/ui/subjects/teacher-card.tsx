"use client";

import { Subject } from "@/app/lib/types";
import { Button } from "@/app/ui/button";
import { ReactElement, useRef, useState } from "react";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { useToast } from "@/app/ui/toast/toast-provider";
import { useConfirm } from "@/app/ui/modals/confirm/confirm-provider";
import Cookies from "universal-cookie";
import { SubjectInputTeacher } from "@/app/ui/inputs/subject-input-teacher";
import CommentTeacher from "@/app/ui/subjects/comment-teacher";

interface TeacherCardProps {
    subjectData: Subject;
    onUpdateAction: () => void;
}

export default function TeacherCard({ subjectData, onUpdateAction }: TeacherCardProps): ReactElement {
    const toast = useToast();
    const confirm = useConfirm();
    const [comment, setComment] = useState<string | null>(subjectData.comment);
    const cookies = new Cookies();
    const commentRef = useRef<HTMLTextAreaElement | null>(null);

    const rejectSubject = async (): Promise<void> => {
        if (comment?.trim() === "" || !comment) {
            toast.showToast({
                type: "warning",
                title: "Commentaire requis",
                message: "Vous devez fournir un commentaire avant de rejeter cette question.",
            });
            return;
        }

        if (
            !(await confirm.showConfirm({
                title: "Confirmer le rejet",
                message:
                    "Une fois la question rejetée l'élève devra en soumettre une nouvelle." +
                    " Voulez-vous quand même rejeter cette question ?",
            }))
        )
            return;

        if (comment?.trim() === subjectData.comment.trim()) {
            const confirmed = await confirm.showConfirm({
                title: "Confirmer le rejet",
                message: "Le commentaire n’a pas été modifié. Voulez-vous quand même rejeter cette question ?",
            });

            if (!confirmed) {
                requestAnimationFrame(() => {
                    commentRef?.current?.blur();
                    commentRef?.current?.focus();
                });
                return;
            }
        }

        const csrfToken = await fetchCsrfClient("reject-subject");

        const payload = {
            "teacher-token": subjectData.teacherToken,
            "student-token": subjectData.studentToken,
            topic: subjectData.topic,
            comment: comment,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("subjects/reject", {
            method: "POST",
            credentials: "include",
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (data?.toast) {
            toast.showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });
        }

        if (data.toastType === "info" && res.ok) {
            cookies.set("subjects-synced-teacher", false, { path: "/" });
            onUpdateAction();
        }
    };

    const approveSubject = async (): Promise<void> => {
        if (
            !(await confirm.showConfirm({
                title: "Validation de la question",
                message:
                    "Êtes-vous sûr de vouloir valider cette question de manière définitive. Une fois la question " +
                    "validée, toute modification devient impossible.",
            }))
        )
            return;

        const csrfToken = await fetchCsrfClient("approve-subject");

        const payload = {
            "teacher-token": subjectData.teacherToken,
            "student-token": subjectData.studentToken,
            topic: subjectData.topic,
            "new-status": "approved",
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("subjects/approve", {
            method: "POST",
            credentials: "include",
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (data?.toast) {
            toast.showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });

            if (data.toastType === "info" && res.ok) {
                cookies.set("subjects-synced-teacher", false, { path: "/" });
                onUpdateAction();
            }
        }
    };

    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <strong>{subjectData.student}</strong>
                <strong>{subjectData.topic}</strong>
            </div>
            <SubjectInputTeacher
                id={subjectData.studentToken + subjectData.teacherToken + "-comment"}
                subjectData={subjectData}
                label="Question de l'Elève"
            />
            <CommentTeacher
                key={`comment-teacher-for-${subjectData.student}-${subjectData.topic}`}
                subjectData={subjectData}
                disabled={subjectData.status !== "submitted"}
                ref={commentRef}
                onChange={(e) => {
                    setComment(e.target.value);
                }}
            />
            {subjectData.status === "submitted" && (
                <>
                    <Button
                        className="-mb-1! mt-1! shadow-none!"
                        text="Ne pas valider la question"
                        type="button"
                        onClick={rejectSubject}
                        color="red"
                    />
                    <Button
                        className="mb-1! mt-1! shadow-none!"
                        text="Valider la question"
                        type="button"
                        onClick={approveSubject}
                        color="green"
                    />
                </>
            )}
        </div>
    );
}
