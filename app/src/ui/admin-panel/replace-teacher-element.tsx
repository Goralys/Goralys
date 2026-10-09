import { ReactElement, useState } from "react";
import { FloatingInput } from "@/app/src/ui/inputs/floating-input";
import { Button } from "@/app/src/ui/button";
import Dropdown from "@/app/src/ui/basic/dropdown";

interface Props {
    onReplaceAction: (firstName: string, lastName: string) => void;
    dropDown?: boolean;
}

export default function ReplaceTeacherElement({ onReplaceAction, dropDown = true }: Props): ReactElement {
    const [firstName, setFirstName] = useState("");
    const [lastName, setLastName] = useState("");

    return dropDown ? (
        <Dropdown label="Remplacer le professeur">
            <div className="flex flex-col gap-2 mt-2">
                <p className="-mb-2">Entrer le nom du nouveau professeur</p>
                <div className="flex flex-row min-w-full gap-x-2.5">
                    <FloatingInput id="replace-teacher-firstname" label="Prénom" onInput={(e) => setFirstName(e.currentTarget.value)} />
                    <FloatingInput id="replace-teacher-lastname" label="Nom" onInput={(e) => setLastName(e.currentTarget.value)} />
                </div>
            </div>

            <Button type="button" text="Remplacer" onClick={() => onReplaceAction(firstName, lastName)} />
        </Dropdown>
    ) : (
        <>
            <div className="flex flex-col gap-2 mt-2">
                <p className="-mb-2">Entrer le nom du nouveau professeur</p>
                <FloatingInput id="replace-teacher-firstname" label="Prénom" onInput={(e) => setFirstName(e.currentTarget.value)} />
                <FloatingInput id="replace-teacher-lastname" label="Nom" onInput={(e) => setLastName(e.currentTarget.value)} />
            </div>

            <Button type="button" text="Remplacer" onClick={() => onReplaceAction(firstName, lastName)} />
        </>
    );
}
