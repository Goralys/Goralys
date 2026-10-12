"use client";

import { clsx } from "clsx";
import { Button } from "#lib/ui/button";
import { Checkbox } from "#lib/ui/inputs/checkbox";
import { QuestionMarkCircleIcon } from "@heroicons/react/24/outline";
import { useModalClose } from "#lib/lib/modals";
import { ReactElement, useRef, useState } from "react";

interface Props {
    savedUsername: string;
    visible: boolean;
    onConfirmAction: (askAgain: boolean) => void;
    onCancelAction: () => void;
}

export function ConfirmTokenElement({ savedUsername, visible, onConfirmAction, onCancelAction }: Props): ReactElement {
    const [doNotAskAgain, setDoNotAskAgain] = useState<boolean>(true);
    const modalRef = useRef<HTMLDivElement>(null);
    useModalClose(modalRef, visible, onCancelAction);

    return (
        <div
            ref={modalRef}
            className={clsx(
                "fixed flex flex-col gap-2 p-3 sm:w-115 w-96 bg-sky-200 rounded shadow overflow-hidden left-1/2 -translate-x-1/2 top-1 ",
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
                    <strong className="text-md">Connexion</strong>
                    <span className="text-sm">Voulez-vous utiliser le compte sauvegardé ({savedUsername}) pour vous connecter ?</span>
                </div>
            </div>

            <Checkbox label="Ne plus me demander" setValueAction={setDoNotAskAgain} defaultValue={doNotAskAgain} />

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
                    onClick={() => onConfirmAction(!doNotAskAgain)}
                />
            </div>
        </div>
    );
}
