import { ReactElement, ReactNode } from "react";

interface Props {
    label: string | ReactNode;
    children?: ReactNode;
    openDefault?: boolean;
}

export default function Dropdown({ label, children, openDefault = false }: Props): ReactElement {
    return (
        <details className="group/details" open={openDefault}>
            <summary className="flex flex-row cursor-pointer">
                <svg
                    className={"w-5 h-5 text-gray-900 transition group-open/details:rotate-90"}
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                >
                    <path
                        fillRule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"
                    ></path>
                </svg>
                {typeof label === "object" ? label : <span>{label}</span>}
            </summary>

            {children}
        </details>
    );
}
