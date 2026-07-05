"use client";

import {
    cookiesSet,
    fetchCsrfClient,
    getStatusLabel,
    goralysFetchClient,
    handleToastRequest,
    Subject,
    SubjectStatus,
    SubjectStatusConfig,
} from "@goralys/core";
import { SubjectInputAdmin } from "@/app/src/ui/inputs/subject-input-admin";
import React, { ReactElement } from "react";
import { usePasswordModal } from "@/app/src/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/src/ui/toast/toast-provider";

interface SubjectsAdminCardProps {
    subjectData: Subject;
    onUpdateAction: () => void;
    syncKey: string;
}

export default function AdminCard({ subjectData, onUpdateAction, syncKey }: SubjectsAdminCardProps): ReactElement {
    const password = usePasswordModal();
    const toast = useToast();

    const updateStatus = async (status: SubjectStatus): Promise<void> => {
        if (status === subjectData.status) return;

        const pwd = await password.showPasswordModal("le changement de statut");

        if (!pwd) return;

        if (pwd.trim() === "") {
            toast.showToast({
                type: "warning",
                title: "Mot de passe",
                message: "Veuillez saisir un mot de passe.",
            });
            return;
        }

        const csrfToken = await fetchCsrfClient("update-subject-status");
        const payload = {
            student: subjectData.studentToken,
            teacher: subjectData.teacherToken,
            status: status,
            topic: subjectData.topic,
            "admin-password": pwd,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("PATCH", "subjects/status", payload);
        await handleToastRequest(res, toast.showToast, false);
        const data = await res?.json();

        if (data.toastType === "info" && res.ok) {
            cookiesSet(syncKey, "0");
            onUpdateAction();
        }
    };

    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <strong>{subjectData.student}</strong>
                <strong>
                    {subjectData.topic} ({subjectData.teacher})
                </strong>
            </div>
            <SubjectInputAdmin
                id={subjectData.studentToken + subjectData.teacherToken + "-input"}
                subjectData={subjectData}
                label="Question de l'Elève"
            />
            <div className="flex flex-row justify-between">
                <p>Statut de la question: {getStatusLabel(subjectData.status)}</p>
                <div className="relative pb-0 mb-1">
                    <select
                        className="border-0 border-b-2 border-sky-300 appearance-none
                    cursor-pointer outline-none focus:ring-0 text-base leading-5
                    text-heading pb-0 pr-5 subjects-search-select"
                        value={subjectData.status}
                        onChange={(e) => updateStatus(e.target.value as SubjectStatus)}
                    >
                        {Object.entries(SubjectStatusConfig).map(([key, info]) => (
                            <option value={key} key={key}>
                                {info.label}
                            </option>
                        ))}
                    </select>

                    <span
                        className="absolute bottom-0 left-0 w-0 h-0.5 bg-sky-500
                     transition-all duration-300 ease-in-out
                     peer-focus:w-full subjects-search-underline"
                    />
                </div>
            </div>
        </div>
    );
}
