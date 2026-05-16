"use client";

import { getStatusLabel, Subject, SubjectStatus, SubjectStatusConfig } from "@/app/lib/types";
import { SubjectInputAdmin } from "@/app/ui/inputs/subject-input-admin";
import React, { ReactElement } from "react";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { usePasswordModal } from "@/app/ui/modals/password/password-modal-provider";
import { useToast } from "@/app/ui/toast/toast-provider";
import Cookies from "universal-cookie";

interface SubjectsAdminCardProps {
    subjectData: Subject;
    onUpdateAction: () => void;
    syncKey: string;
}

export default function AdminCard({ subjectData, onUpdateAction, syncKey }: SubjectsAdminCardProps): ReactElement {
    const password = usePasswordModal();
    const toast = useToast();
    const cookies = new Cookies();

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
            "student-token": subjectData.studentToken,
            "teacher-token": subjectData.teacherToken,
            status: status,
            topic: subjectData.topic,
            "admin-password": pwd,
            "csrf-token": csrfToken,
        };

        const res = await goralysFetchClient("subjects/status", {
            method: "PATCH",
            body: JSON.stringify(payload),
        });

        const data = await res?.json();

        if (data?.toast) {
            toast.showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });
        }

        if (data.toastType === "info" && res.ok) {
            cookies.set(syncKey, "0", { path: "/" });
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
