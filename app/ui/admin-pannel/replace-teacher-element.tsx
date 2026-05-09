import { ReactElement, useState } from "react";
import { FloatingInput } from "@/app/ui/inputs/floating-input";
import { Button } from "@/app/ui/button";

interface ReplaceTeacherElementProps {
    onReplaceAction: (firstName: string, lastName: string) => void;
}

export default function ReplaceTeacherElement({ onReplaceAction }: ReplaceTeacherElementProps): ReactElement {
    const [firstName, setFirstName] = useState("");
    const [lastName, setLastName] = useState("");

    return (
        <details className="group/details">
            <summary className="flex flex-row cursor-pointer">
                <svg
                    className="w-5 h-5 text-gray-900 transition group-open/details:rotate-90"
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
                <span>Remplacer le professeur</span>
            </summary>

            <div className="flex flex-col gap-2 mt-2">
                <p className="-mb-2">Entrer le nom du nouveau professeur</p>
                <div className="flex flex-row min-w-full gap-x-2.5">
                    <FloatingInput id="replace-teacher-firstname" label="Prénom" onInput={(e) => setFirstName(e.currentTarget.value)} />
                    <FloatingInput id="replace-teacher-lastname" label="Nom" onInput={(e) => setLastName(e.currentTarget.value)} />
                </div>
            </div>

            <Button type="button" text="Remplacer" onClick={() => onReplaceAction(firstName, lastName)} />
        </details>
    );
}
