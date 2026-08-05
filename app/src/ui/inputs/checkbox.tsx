"use client";

import { ReactElement } from "react";

interface Props {
    id?: string;
    label: string;
    setValueAction: (v: boolean) => void;
    defaultValue: boolean;
    className?: string | null;
    disabled?: boolean;
}

export default function Checkbox({ id, label, setValueAction, defaultValue, className, disabled = false }: Props): ReactElement {
    return (
        <div className={`flex items-center gap-0.5 ${className ?? ""}`}>
            <label
                className={`relative flex items-center justify-center rounded-full p-1 ${disabled ? "cursor-not-allowed" : "cursor-pointer"}`}
                htmlFor="ripple-on"
                data-ripple-dark="true"
            >
                <input
                    disabled={disabled}
                    defaultChecked={defaultValue}
                    onChange={(e) => setValueAction(e.target.checked)}
                    id={id}
                    type="checkbox"
                    className="peer h-4 w-4 appearance-none rounded border border-sky-400
                    bg-white shadow hover:shadow-md transition-all
                    before:absolute before:top-2/4 before:left-2/4 before:block
                    before:h-5 before:w-5 before:-translate-y-2/4 before:-translate-x-2/4 before:rounded-full
                    before:bg-sky-500 before:opacity-0 before:transition-opacity
                    checked:border-sky-600 checked:bg-sky-600
                    checked:before:bg-sky-500 hover:before:opacity-10
                    cursor-pointer disabled:cursor-not-allowed"
                />
                <span
                    className="pointer-events-none absolute inset-0 flex items-center justify-center
                text-white opacity-0 transition-opacity peer-checked:opacity-100"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-3.5 w-3.5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        stroke="currentColor"
                        strokeWidth="1"
                    >
                        <path
                            fillRule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8
                            12.586l7.293-7.293a1 1 0 011.414 0z"
                            clipRule="evenodd"
                        />
                    </svg>
                </span>
            </label>

            <label className={`text-black text-sm `} htmlFor="ripple-on">
                {label}
            </label>
        </div>
    );
}
