"use client";

import { clsx } from "clsx";
import { Button } from "@/app/src/ui/button";
import { QuestionMarkCircleIcon } from "@heroicons/react/24/outline";
import { useModalClose } from "@/app/src/lib/modals";
import { ReactElement, useRef } from "react";

interface ConfirmProps {
    title: string;
    message: string;
    visible: boolean;
    onConfirmAction: () => void;
    onCancelAction: () => void;
}

export default function ConfirmElement({ title, message, visible, onConfirmAction, onCancelAction }: ConfirmProps): ReactElement {
    const modalRef = useRef<HTMLDivElement>(null);
    useModalClose(modalRef, visible, onCancelAction);

    return (
        <div
            ref={modalRef}
            className={clsx(
                "fixed flex flex-col gap-2 p-3 w-115 bg-sky-200 rounded shadow overflow-hidden left-1/2 -translate-x-1/2 top-1 ",
                "after:absolute after:left-0 after:top-0 after:h-full after:w-1.25 after:content-[''] after:bg-blue-500",
                "transition-all duration-500 z-50 ",
                {
                    "translate-y-0 opacity-100": visible,
                    "-translate-y-5 opacity-0": !visible,
                },
            )}
            role="dialog"
            aria-modal="true"
        >
            <div className="flex gap-3">
                <div className="w-11 h-11 flex self-center items-center justify-center">
                    <QuestionMarkCircleIcon className="size-15 text-blue-500" />
                </div>

                <div className="flex flex-col justify-center flex-1">
                    <strong className="text-md">{title}</strong>
                    <span className="text-sm">{message}</span>
                </div>
            </div>

            <div className="flex justify-between gap-2 mt-2">
                <Button
                    className="bg-gray-400! before:bg-gray-500! text-white! border-none! shadow-none! mt-0! mb-0! w-25!"
                    text="Annuler"
                    type="button"
                    onClick={onCancelAction}
                />
                <Button
                    className="bg-blue-500! before:bg-blue-600! text-white! border-none! shadow-none! mt-0! mb-0! w-25!"
                    text="Confirmer"
                    type="button"
                    onClick={onConfirmAction}
                />
            </div>
        </div>
    );
}
