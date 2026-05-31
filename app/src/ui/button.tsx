import React, { ReactElement } from "react";
import clsx from "clsx";

interface ButtonProps {
    className?: string;
    text: string;
    type: "submit" | "button" | "reset";
    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    color?: "sky" | "red" | "green" | "amber";
    disabled?: boolean;
}

const colors: Record<NonNullable<ButtonProps["color"]>, string> = {
    sky: "bg-sky-100 border-sky-300 before:bg-sky-300",
    red: "bg-red-50 border-red-300 before:bg-red-300",
    green: "bg-green-50 border-green-300 before:bg-green-300",
    amber: "bg-amber-50 border-amber-300 before:bg-amber-300",
};

export function Button({ text, type, className, onClick, color = "sky", disabled = false }: ButtonProps): ReactElement {
    return (
        <button
            type={type}
            className={clsx(
                `
            relative block w-full h-10 ${colors[color]} border mt-2 mb-2 rounded-xs shadow-lg z-0
            transition-colors duration-500 overflow-hidden

            before:w-0 before:h-full before:content-[''] before:absolute before:left-0 before:top-0 before:z-0
            before:transition-all before:duration-500
            hover:text-gray-900 hover:before:w-full
            ${className}
        `,
                {
                    "bg-gray-200! border-gray-500! before:bg-transparent cursor-not-allowed": disabled,
                },
            )}
            onClick={onClick}
            disabled={disabled}
        >
            <p className="relative text-[17px] z-10">{text}</p>
        </button>
    );
}
