"use client";

import { ReactElement, useMemo, useState } from "react";
import { useFilterOptions } from "@/app/src/hooks/use-subjects-filter-options";
import {
    fetchCsrfClient,
    getStatusLabel,
    goralysFetchClient,
    handleToastRequest,
    Subject,
    SubjectStatus,
    useSubjects,
} from "@goralys/core";
import ReadonlyCard from "@/app/src/ui/subjects/readonly-card";
import Checkbox from "@/app/src/ui/inputs/checkbox";
import { useConfirm } from "@/app/src/ui/modals/confirm/confirm-provider";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Button } from "@/app/src/ui/button";
import Loading from "@/app/loading";
import Dropdown from "@/app/src/ui/basic/dropdown";

const ALL_STATUSES: SubjectStatus[] = ["not_submitted", "submitted", "rejected", "approved"];

const COLS: Record<number, string> = {
    1: "grid-cols-1",
    2: "grid-cols-2",
    3: "grid-cols-3",
    4: "grid-cols-4",
};

type FilterGroupProps<T extends string | number> = {
    title: string;
    options: T[];
    selected: T[];
    onToggle: (v: T) => void;
    onToggleAll: (checkAll: boolean) => void;
    getLabel?: (v: T) => string;
    columns?: number;
    expendDefault?: boolean;
};

function FilterGroup<T extends string | number>({
    title,
    options,
    selected,
    onToggle,
    onToggleAll,
    getLabel = String,
    columns = 2,
    expendDefault = false,
}: FilterGroupProps<T>): ReactElement {
    const allSelected = options.length > 0 && options.every((o) => selected.includes(o));

    return (
        <fieldset className="mb-5">
            <Dropdown
                openDefault={expendDefault}
                label={
                    <div className="mb-1 flex items-center gap-1">
                        <legend className="float-left font-semibold">{title}</legend>
                        {/* Le key force le remontage : Checkbox est non contrôlé (defaultChecked) */}
                        <Checkbox
                            key={`all-${allSelected}`}
                            label=""
                            defaultValue={allSelected}
                            setValueAction={() => onToggleAll(!allSelected)}
                        />
                    </div>
                }
            >
                <div className={`grid ${COLS[columns]} gap-x-2`}>
                    {options.map((o) => {
                        const checked = selected.includes(o);
                        return (
                            <Checkbox
                                key={`${o}-${checked}`}
                                label={getLabel(o)}
                                defaultValue={checked}
                                setValueAction={() => onToggle(o)}
                            />
                        );
                    })}
                </div>
            </Dropdown>
        </fieldset>
    );
}

export default function AdminExportPageClient(): ReactElement {
    const confirm = useConfirm();
    const toast = useToast();

    const { opt } = useFilterOptions();
    const { subjects } = useSubjects("admin");

    const [excludedClassrooms, setExcludedClassrooms] = useState<string[]>([]);
    const [excludedTopics, setExcludedTopics] = useState<string[]>([]);
    const [currentStatuses, setCurrentStatuses] = useState<SubjectStatus[]>(["approved"]);

    const classroomOptions = useMemo(() => Object.keys(opt?.classrooms ?? {}), [opt]);
    const topicOptions = useMemo(() => Object.keys(opt?.topics ?? {}), [opt]);

    const currentClassrooms = useMemo(
        () => classroomOptions.filter((c) => !excludedClassrooms.includes(c)),
        [classroomOptions, excludedClassrooms],
    );
    const currentTopics = useMemo(() => topicOptions.filter((t) => !excludedTopics.includes(t)), [topicOptions, excludedTopics]);

    const { currentSubjects, studentCount } = useMemo(() => {
        if (!subjects || !opt) return { currentSubjects: [] as Subject[], studentCount: 0 };

        const matching = subjects.filter(
            (s) =>
                currentStatuses.includes(s.status) &&
                currentClassrooms.some((c) => opt.classrooms[c]?.includes(s.studentToken)) &&
                currentTopics.some((t) => opt.topics[t]?.includes(s.studentToken)),
        );
        const students = new Set(matching.map((s) => s.studentToken));

        return {
            currentSubjects: subjects.filter((s) => students.has(s.studentToken)),
            studentCount: students.size,
        };
    }, [subjects, opt, currentStatuses, currentClassrooms, currentTopics]);

    const toggle = <T,>(list: T[], v: T): T[] => (list.includes(v) ? list.filter((x) => x !== v) : [v, ...list]);

    const toggleStatus = (s: SubjectStatus): void => setCurrentStatuses((p) => toggle(p, s));
    const toggleClassroom = (c: string): void => setExcludedClassrooms((p) => toggle(p, c));
    const toggleTopic = (t: string): void => setExcludedTopics((p) => toggle(p, t));

    // Statuts : on stocke les cochés. Classes/topics : on stocke les exclus.
    const toggleAllStatuses = (check: boolean): void => setCurrentStatuses(check ? ALL_STATUSES : []);
    const toggleAllClassrooms = (check: boolean): void => setExcludedClassrooms(check ? [] : classroomOptions);
    const toggleAllTopics = (check: boolean): void => setExcludedTopics(check ? [] : topicOptions);
    const getStatusVal = (s: SubjectStatus): number => {
        switch (s) {
            case "not_submitted":
                return 0;
            case "submitted":
                return 1;
            case "rejected":
                return 2;
            case "approved":
                return 3;
        }
    };

    const exportSubjects = async (): Promise<void> => {
        if (
            !(await confirm.showConfirm({
                title: "Export des sujets",
                message: "Ête-vous sûr de vouloir exporter les sujets ? Cette opération peut prendre quelques minutes.",
            }))
        )
            return;

        const csrfToken = await fetchCsrfClient("export-subjects");
        const payload = {
            "csrf-token": csrfToken,
            status: currentStatuses.map((s) => getStatusVal(s)),
            classrooms: currentClassrooms,
            topics: currentTopics,
        };

        const res = await goralysFetchClient("POST", "subjects/export", payload);

        if (res.ok) {
            const blob = await res.blob();

            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "sujets-go.zip";
            a.click();

            URL.revokeObjectURL(url);
            return;
        }

        await handleToastRequest(res, toast.showToast, false);
    };

    return !opt || !subjects ? (
        <Loading />
    ) : (
        <div className="grid grid-cols-[30rem_minmax(0,1fr)_30rem] items-stretch gap-6 ml-3 w-full">
            <aside className="sticky top-10 overflow-y-auto border-r border-gray-300 pr-6 pt-10">
                <FilterGroup
                    title="Statut"
                    options={ALL_STATUSES}
                    selected={currentStatuses}
                    onToggle={toggleStatus}
                    onToggleAll={toggleAllStatuses}
                    getLabel={(s) => getStatusLabel(s) + "s"}
                    columns={2}
                />
                <FilterGroup
                    title="Classe"
                    options={classroomOptions}
                    selected={currentClassrooms}
                    onToggle={toggleClassroom}
                    onToggleAll={toggleAllClassrooms}
                />
                <FilterGroup
                    title="Groupes de spécialité"
                    options={topicOptions}
                    selected={currentTopics}
                    onToggle={toggleTopic}
                    onToggleAll={toggleAllTopics}
                    columns={3}
                    expendDefault={true}
                />

                <Button text="Exporter ces sujets" type="button" onClick={exportSubjects} />
            </aside>

            <main className="mx-auto flex w-full max-w-3xl flex-col gap-4 pt-10">
                <p>
                    Prévisualisation des sujets exportés ({studentCount} fiche{studentCount > 1 ? "s" : ""} · {currentSubjects.length} sujet
                    {currentSubjects.length > 1 ? "s" : ""})
                </p>
                {currentSubjects.map((s) => (
                    <ReadonlyCard key={s.teacherToken + s.studentToken + s.topicCode} subjectData={s} />
                ))}
            </main>

            <div aria-hidden />
        </div>
    );
}
