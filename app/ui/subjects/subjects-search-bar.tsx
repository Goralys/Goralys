import { FloatingInput } from "@/app/ui/inputs/floating-input";
import React, { FormEvent, ReactElement, useEffect, useState } from "react";
import { searchFields, Subject, SubjectsSearchField } from "@/app/lib/types";
import { getLongFromShort } from "@/app/lib/subjects/subjects-utils";

interface SubjectsSearchBarProps {
    subjects: Subject[] | null;
    setCurrentSubjects: React.Dispatch<React.SetStateAction<Subject[] | null>>;
}

export function SubjectsSearchBar({ subjects, setCurrentSubjects }: SubjectsSearchBarProps): ReactElement {
    const [currentField, setCurrentField] = useState<SubjectsSearchField>("all");
    const [searchText, setSearchText] = useState("");

    const handleSearch = (e: FormEvent<HTMLInputElement>): void => {
        const value = e.currentTarget.value;
        setSearchText(value);
    };

    const sortSubjects = (list: Subject[]): Subject[] => {
        return [...list].sort((a, b) => {
            const topicA = a.topic.trim().toLowerCase();
            const topicB = b.topic.trim().toLowerCase();
            const nameA = a.student.trim().toLowerCase();
            const nameB = b.student.trim().toLowerCase();

            const topicDiff = topicA.localeCompare(topicB, "fr");
            if (topicDiff !== 0) return topicDiff;
            return nameA.localeCompare(nameB, "fr");
        });
    };

    useEffect(() => {
        if (!subjects) return;

        const search = searchText.trim().toLowerCase();
        const sorted = sortSubjects(
            subjects.filter((s: Subject) => {
                if (!search) return true;
                const searchTeachers = s.teacher.split(",").map((t) => t.trim().toLowerCase());

                switch (currentField) {
                    case "student":
                        return s.student.trim().toLowerCase().startsWith(search);
                    case "teacher":
                        return searchTeachers.some((t) => t.startsWith(search));
                    case "topic":
                        return s.topic.trim().toLowerCase().includes(getLongFromShort(search));
                    case "all":
                        return (
                            s.student.trim().toLowerCase().startsWith(search) ||
                            searchTeachers.some((t) => t.startsWith(search)) ||
                            s.topic.trim().toLowerCase().includes(getLongFromShort(search))
                        );
                    default:
                        return true;
                }
            }),
        );

        setCurrentSubjects((prev) => {
            if (JSON.stringify(prev) === JSON.stringify(sorted)) return prev;
            return sorted;
        });
    }, [searchText, currentField, subjects, setCurrentSubjects]);

    return (
        <div className="flex flex-row gap-2 items-end mb-4">
            <FloatingInput id="admins-subjects-search" label="Rechercher" onInput={handleSearch} />

            <div className="relative pb-0 mb-1">
                <select
                    className="border-0 border-b-2 border-sky-300 appearance-none
                    cursor-pointer outline-none focus:ring-0 text-base leading-5
                    text-heading pb-0 pr-5 subjects-search-select"
                    value={currentField}
                    onChange={(e) => setCurrentField(e.target.value as SubjectsSearchField)}
                >
                    {Object.entries(searchFields).map(([key, label]) => (
                        <option value={key} key={key}>
                            {label}
                        </option>
                    ))}
                </select>

                <span
                    className="absolute bottom-0 left-0 w-0 h-0.5 bg-sky-500
                     transition-all duration-300 ease-in-out
                     peer-focus:w-full subjects-search-underline"
                />
            </div>
        </div>
    );
}
