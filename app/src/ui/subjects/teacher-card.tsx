"use client";

import {
    cookiesSet,
    fetchCsrfClient,
    getShortFromLong,
    goralysFetchClient,
    handleToastRequest,
    Subject,
    SUBJECT_SYNCS,
} from "@goralys/core";
import { Button } from "@/app/src/ui/button";
import { ReactElement, useRef, useState } from "react";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";
import { SubjectInputTeacher } from "@/app/src/ui/inputs/subject-input-teacher";
import CommentTeacher from "@/app/src/ui/subjects/comment-teacher";
import { useMediaQuery } from "@/app/src/hooks/use-media-query";

interface Props {
    subjectData: Subject;
    onUpdateAction: () => void;
}

export default function TeacherCard({ subjectData, onUpdateAction }: Props): ReactElement {
    const toast = useToast();
    const confirm = useConfirm();
    const [comment, setComment] = useState<string | null>(subjectData.comment);
    const commentRef = useRef<HTMLTextAreaElement | null>(null);
    const isDesktop = useMediaQuery("(min-width: 640px)");

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
                    "Une fois la question rejetée, l'élève devra en soumettre une nouvelle." +
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
            teacher: subjectData.teacherToken,
            student: subjectData.studentToken,
            topic: subjectData.topic,
            comment: comment,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("POST", "subjects/reject", payload);

        if (await handleToastRequest(res, toast.showToast, false)) {
            const data = await res.json();
            if (data.toastType === "info" && res.ok) {
                cookiesSet(SUBJECT_SYNCS["teacher"], "0");
                onUpdateAction();
            }
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
            teacher: subjectData.teacherToken,
            student: subjectData.studentToken,
            topic: subjectData.topic,
            "new-status": "approved",
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("POST", "subjects/approve", payload);

        if (await handleToastRequest(res, toast.showToast, false)) {
            const data = await res.json();
            if (data.toastType === "info" && res.ok) {
                cookiesSet(SUBJECT_SYNCS["teacher"], "0");
                onUpdateAction();
            }
        }
    };

    return (
        <div className="h-fit sm:w-200 w-96 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <strong>{subjectData.student}</strong>
                <strong>{isDesktop ? subjectData.topic : getShortFromLong(subjectData.topic)}</strong>
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
