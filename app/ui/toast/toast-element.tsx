'use client';

import {clsx} from "clsx";
import {ToastProps} from "@/app/lib/types";
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    XCircleIcon,
    InformationCircleIcon
} from "@heroicons/react/24/outline";

const icons = {
    info:    <InformationCircleIcon className="size-14 p-0 -ml-1.5 -mr-1.5 text-blue-600" />,
    success: <CheckCircleIcon       className="size-14 p-0 -ml-1.5 -mr-1.5 text-green-600" />,
    warning: <ExclamationCircleIcon className="size-14 p-0 -ml-1.5 -mr-1.5 text-amber-600" />,
    error:   <XCircleIcon           className="size-14 p-0 -ml-1.5 -mr-1.5 text-red-600" />,
};

export default function ToastElement({ type, title, message, visible }: ToastProps) {
    return (
        <div
            className={clsx(
                "fixed flex gap-2 p-3 h-22 w-115 bg-sky-300 rounded shadow overflow-hidden left-1/2 -translate-x-1/2 top-1 ",
                "after:absolute after:left-0 after:top-0 after:h-full after:w-1.25 after:content-['']",
                "transition-all duration-500 z-10",
                {
                    "after:bg-blue-600": type === "info",
                    "after:bg-green-600": type === "success",
                    "after:bg-amber-600": type === "warning",
                    "after:bg-red-600": type === "error",
                },
                {
                    "translate-y-0 opacity-100": visible,
                    "-translate-y-5 opacity-0": !visible,
                }
            )}
            role="alert"
        >
            <div className="flex gap-3">
                <div className="flex self-center">
                    {icons[type]}
                </div>

                <div className="flex flex-col justify-center">
                    <strong className="text-md">{title}</strong>
                    <span className="text-sm">{message}</span>
                </div>
            </div>
        </div>
    );
}