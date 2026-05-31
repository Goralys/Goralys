"use client";

import { Subject } from "@/app/lib/types";
import { SubjectInputStudent } from "@/app/ui/inputs/subject-input-student";
import { Button } from "@/app/ui/button";
import { ReactElement, useState } from "react";
import CommentStudent from "@/app/ui/subjects/comment-student";
import { fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/lib/fetch/fetch.client";
import { useToast } from "@/app/ui/toast/toast-provider";
import Cookies from "universal-cookie";
import { useDraftModal } from "@/app/ui/modals/drafts/draft-modal-provider";
import { useConfirm } from "@/app/ui/modals/confirm/confirm-provider";

interface StudentCardProps {
    subjectData: Subject;
    onUpdateAction: () => void;
}

export default function StudentCard({ subjectData, onUpdateAction }: StudentCardProps): ReactElement {
    const toast = useToast();
    const confirm = useConfirm();
    const [subject, setSubject] = useState<string | null>(subjectData.subject);
    const [isInterdisciplinary, setIsInterdisciplinary] = useState<boolean>(subjectData.interdisciplinary);
    const modal = useDraftModal();
    const cookies = new Cookies();

    const saveDraft = async (): Promise<void> => {
        const csrfToken = await fetchCsrfClient("save-draft");

        const payload = {
            teacher: subjectData.teacherToken,
            student: subjectData.studentToken,
            topic: subjectData.topic,
            draft: subject,
            interdisciplinary: isInterdisciplinary,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("PUT", "subjects/draft", payload);
        await handleToastRequest(res, toast.showToast, false);
        const data = await res.json();

        if (data.toastType === "info" && res.ok) {
            cookies.set("subjects-synced-student", "0", { path: "/" });
            onUpdateAction();
        }
    };

    const sendSubject = async (): Promise<void> => {
        if (!subject || subject.trim() == "") {
            toast.showToast({
                type: "warning",
                title: "Envoi",
                message: "Veuillez saisir une question.",
            });
            return;
        }

        if (
            !(await confirm.showConfirm({
                title: "Envoi",
                message: "Êtes-vous sûr de vouloir envoyer cette question au professeur ?",
            }))
        )
            return;

        if (subject?.trim() === subjectData.lastRejected?.trim()) {
            toast.showToast({
                type: "warning",
                title: "Envoi",
                message: "Cette question n’a pas été modifiée depuis son invalidation. Merci de la corriger avant de la renvoyer.",
            });
            return;
        }

        const csrfToken = await fetchCsrfClient("submit-subject");
        const result = await modal.showDraftModal();

        if (result.type === "closed") return;

        if (result.type == "withDraft" && !result.file) {
            toast.showToast({
                type: "warning",
                title: "Envoi",
                message: "Veuillez choisir un brouillon ou envoyer la question seule.",
            });
            return;
        }

        const formData = new FormData();
        formData.append("teacher", subjectData.teacherToken);
        formData.append("student", subjectData.studentToken);
        formData.append("topic", subjectData.topic);
        formData.append("subject", subject ?? "");
        formData.append("csrf-token", csrfToken ?? "");
        formData.append("interdisciplinary", isInterdisciplinary ? "1" : "0");

        if (result.type == "withDraft") {
            formData.append("draft-file", result.file ?? "");
        }

        const res = await goralysFetchClient("POST", "subjects/submit", formData);

        if (await handleToastRequest(res, toast.showToast, false)) {
            const data = await res.json();
            if (data.toastType === "info" && res.ok) {
                cookies.set("subjects-synced-student", "0", { path: "/" });
                onUpdateAction();
            }
        }
    };

    const key = subjectData.teacher + subjectData.topic;
    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <strong>{subjectData.topic}</strong>
                <strong>{subjectData.teacher}</strong>
            </div>

            <SubjectInputStudent
                id={`subject-input-student-for-${key}`}
                label="Votre Question"
                subjectData={subjectData}
                onChangeAction={(e) => {
                    setSubject(e.target.value);
                }}
                setIsInterdisciplinaryAction={setIsInterdisciplinary}
            />
            <CommentStudent key={`comment-student-for-${key}`} subjectData={subjectData} disabled={true} />
            {!(subjectData.status === "submitted" || subjectData.status === "approved") && (
                <>
                    <Button className="mb-1! mt-1!" text="Enregistrer comme brouillon" type="button" onClick={saveDraft} />
                    <Button className="mb-1! mt-1!" text="Envoyer la question" type="button" onClick={sendSubject} />
                </>
            )}
        </div>
    );
}
