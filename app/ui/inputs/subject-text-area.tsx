"use client";

import { clsx } from "clsx";
import React, { ChangeEventHandler, ReactElement, RefObject } from "react";
import { useAutoResize } from "@/app/src/lib/inputs";
import { Subject } from "@/app/src/lib/types";

interface SubjectTextAreaProps {
    defaultValue?: string;
    disabled?: boolean;
    ref?: RefObject<HTMLTextAreaElement | null>;
    helper?: string;
    id: string;
    label: string;
    maxLength?: number;
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>;
    subjectData: Subject;
    animate: boolean;
}

export function SubjectTextArea({
    id,
    label,
    helper,
    disabled = false,
    ref,
    defaultValue,
    maxLength,
    onChangeAction,
    subjectData,
    animate,
}: SubjectTextAreaProps): ReactElement {
    const setRef = useAutoResize(ref);

    return (
        <div
            className={clsx("relative mt-0 mb-0 group min-w-50 w-full", {
                "mb-5!": !!helper,
            })}
        >
            <textarea
                ref={setRef} // ← merged ref: forwards + sets up resize
                id={id}
                name={id}
                rows={1}
                placeholder=" "
                spellCheck="true"
                defaultValue={defaultValue}
                readOnly={disabled}
                maxLength={maxLength}
                onChange={onChangeAction}
                className={clsx(
                    "peer block w-full py-0 px-0 cursor-text text-base text-heading " +
                        "bg-transparent border-0 border-b-2 border-sky-300 " +
                        "appearance-none focus:outline-none focus:ring-0 resize-none overflow-hidden ",
                    {
                        "border-green-600!": subjectData.status === "approved",
                        "border-amber-600!": subjectData.status === "submitted",
                        "border-red-600!": subjectData.status === "rejected",
                    },
                    {
                        "text-gray-700 cursor-not-allowed!": disabled,
                    },
                )}
            />

            {/* Animated underline */}
            {animate && (
                <span
                    className="pointer-events-none
               absolute bg-sky-500 left-0 bottom-0 h-0.5 w-full
               origin-left scale-x-0
               transition-transform duration-250
               group-focus-within:scale-x-100 "
                />
            )}

            <label
                htmlFor={id}
                className={clsx(
                    "absolute text-base text-body cursor-text duration-300 transform " +
                        "-translate-y-4.5 scale-75 top-0 origin-left " +
                        "peer-placeholder-shown:scale-100 " +
                        "peer-placeholder-shown:translate-y-0 " +
                        "peer-focus:scale-75 " +
                        "peer-focus:-translate-y-4.5",
                    { "cursor-not-allowed": disabled },
                )}
            >
                {label}
            </label>

            <p
                className={clsx("mt-0 absolute text-[13px] italic text-gray-600", {
                    hidden: helper === undefined,
                })}
            >
                *{helper}
            </p>
        </div>
    );
}
