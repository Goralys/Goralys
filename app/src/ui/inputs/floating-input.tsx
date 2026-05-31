import { clsx } from "clsx";
import React, { useState, FormEventHandler, ReactElement } from "react";

import { EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";

interface InputProps {
    autocomplete?: string;
    disabled?: boolean;
    helper?: string;
    id: string;
    label: string;
    password?: boolean;
    email?: boolean;
    required?: boolean;
    defaultValue?: string;
    value?: string;
    onInput?: FormEventHandler<HTMLInputElement>;
}

export function FloatingInput({
    id,
    label,
    helper,
    autocomplete,
    password = false,
    email = false,
    disabled = false,
    required = false,
    defaultValue,
    value,
    onInput,
}: InputProps): ReactElement {
    const [show, setShow] = useState<boolean>(!password);
    const [isAutofilled, setIsAutofilled] = useState(false);

    function onEyeClicked(): void {
        setShow(!show);
    }

    function handleAnimationStart(e: React.AnimationEvent<HTMLInputElement>): void {
        if (e.animationName === "onAutoFillStart") setIsAutofilled(true);
        if (e.animationName === "onAutoFillCancel") setIsAutofilled(false);
    }

    return (
        <div
            className={clsx("relative mt-3 group min-w-50 w-full", {
                "mb-5": helper !== undefined,
                "mb-1": helper === undefined,
            })}
        >
            <input
                type={email ? "email" : show ? "text" : "password"}
                id={id}
                name={id}
                placeholder=" "
                autoComplete={autocomplete}
                disabled={disabled}
                required={required}
                defaultValue={defaultValue}
                value={value}
                onInput={onInput}
                onAnimationStart={handleAnimationStart}
                className={clsx(
                    "peer block w-full leading-none py-0 px-0 cursor-text text-base text-heading " +
                        "bg-transparent border-0 border-b-2 border-sky-300 " +
                        "appearance-none focus:outline-none focus:ring-0 ",
                    {
                        "cursor-not-allowed!": disabled,
                    },
                )}
            />

            {/* Animated underline */}
            <span
                className="
              pointer-events-none
              absolute left-0 bottom-0 h-0.5 w-full
              origin-left scale-x-0
              bg-sky-500
              transition-transform duration-250
              group-focus-within:scale-x-100
              "
            />

            <label
                htmlFor={id}
                className={clsx(
                    "absolute text-base text-body cursor-text duration-300 transform " +
                        "-translate-y-4.5 scale-75 top-0 origin-left " +
                        "peer-placeholder-shown:scale-100 " +
                        "peer-placeholder-shown:translate-y-0 " +
                        "peer-focus:scale-75 " +
                        "peer-focus:-translate-y-4.5 ",
                    {
                        "cursor-not-allowed!": disabled,
                        "scale-75! -translate-y-4.5!": isAutofilled, // force active state
                    },
                )}
            >
                {label}
            </label>

            {password && (
                <button type="button" onClick={onEyeClicked} className="absolute top-px right-0 text-gray-900">
                    {show ? <EyeSlashIcon className="size-5" /> : <EyeIcon className="size-5" />}
                </button>
            )}

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
