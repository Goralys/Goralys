"use client";

import { clsx } from "clsx";
import React, { ChangeEventHandler, ReactElement, RefObject, useState } from "react";
import { useAutoResize } from "@/app/src/lib/inputs";

interface Props {
    defaultValue?: string;
    disabled?: boolean;
    ref?: RefObject<HTMLTextAreaElement | null>;
    helper?: string;
    id: string;
    label: string;
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>;
    required?: boolean;
    maxLength?: number;
}

export function TextArea({
    id,
    label,
    helper,
    required = false,
    disabled = false,
    ref,
    defaultValue,
    onChangeAction,
    maxLength,
}: Props): ReactElement {
    const setRef = useAutoResize(ref);
    const [currentValue, setCurrentValue] = useState<string>(defaultValue ?? "");

    const update: ChangeEventHandler<HTMLTextAreaElement> = (e) => {
        if (maxLength && e.target.value.length > maxLength) {
            return;
        }
        setCurrentValue(e.currentTarget.value);
        if (onChangeAction) onChangeAction(e);
    };

    return (
        <div className="relative mt-3 mb-1 group min-w-50">
            <div className="relative">
                <textarea
                    ref={setRef} // ← merged ref: forwards + sets up resize
                    id={id}
                    name={id}
                    rows={1}
                    placeholder=" "
                    spellCheck="true"
                    defaultValue={defaultValue}
                    maxLength={maxLength}
                    readOnly={disabled}
                    onChange={update}
                    required={required}
                    className="
                    peer block w-full py-0 px-0 text-base text-heading
                    resize-none overflow-hidden bg-transparent
                    border-0 border-b-2 border-sky-300
                    appearance-none focus:outline-none focus:ring-0
                "
                />

                <span
                    className="pointer-events-none
               absolute bg-sky-500 left-0 bottom-0 h-0.5 w-full
               origin-left scale-x-0
               transition-transform duration-250
               group-focus-within:scale-x-100 "
                />

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
            </div>
            <div className="flex flex-row content-between w-full">
                <div className="flex flex-col">
                    {maxLength && (
                        <p
                            className={clsx("mt-1 mb-0 p-0 relative text-[11px] italic", {
                                "text-gray-600": currentValue.length < maxLength * 0.9,
                                "text-amber-600": currentValue.length >= maxLength * 0.9 && maxLength > currentValue.length,
                                "text-red-600": currentValue.length >= maxLength,
                            })}
                        >
                            {currentValue.length}/{maxLength} caractères
                        </p>
                    )}
                    {helper && helper.length !== 0 && (
                        <p
                            className={clsx("self-center relative text-[13px] italic text-gray-600", {
                                "mt-1": maxLength === undefined,
                            })}
                        >
                            *{helper}
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
