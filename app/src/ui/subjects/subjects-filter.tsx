import { ReactElement, useCallback, useEffect, useState } from "react";
import { getStatusLabel, Subject, SubjectStatus } from "@goralys/core";
import Checkbox from "@/app/src/ui/inputs/checkbox";

interface Props {
    subjects: Subject[];
    setCurrentSubjects: (s: Subject[]) => void;
    defaults?: Record<SubjectStatus, boolean>;
}

export default function SubjectsFilter({ subjects, setCurrentSubjects, defaults }: Props): ReactElement {
    const DEFAULT_STATUS: Record<SubjectStatus, { default: boolean; label: string }> = {
        not_submitted: { default: defaults?.not_submitted ?? false, label: getStatusLabel("not_submitted") },
        submitted: { default: defaults?.submitted ?? true, label: getStatusLabel("submitted") },
        rejected: { default: defaults?.rejected ?? false, label: getStatusLabel("rejected") },
        approved: { default: defaults?.approved ?? false, label: getStatusLabel("approved") },
    };

    // status sort toggle
    const [shownStatus, setShownStatus] = useState<SubjectStatus[]>(
        (Object.keys(DEFAULT_STATUS) as SubjectStatus[]).filter((k) => DEFAULT_STATUS[k].default),
    );
    const toggleStatus = (s: SubjectStatus): void => {
        console.log("toggling status: ", s);
        const status = shownStatus;
        if (shownStatus.includes(s)) {
            const i = shownStatus.indexOf(s);
            status.splice(i, 1);
        } else {
            status.push(s);
        }

        setShownStatus(status);
        console.log("toggle done, new shown status: ", shownStatus);
        console.log("filtering");
        filter();
        console.log("filtering done");
    };

    // eslint-disable-next-line react-hooks/preserve-manual-memoization
    const filter = useCallback((): void => {
        const filtered: Subject[] = [];

        subjects.forEach((s) => {
            if (shownStatus.includes(s.status)) filtered.push(s);
        });

        console.log(filtered);
        setCurrentSubjects(filtered);
    }, [shownStatus, subjects, setCurrentSubjects]);

    useEffect(() => {
        filter();
    }, [filter]);

    return (
        <div className="flex flex-row gap-2 w-full">
            {Object.entries(DEFAULT_STATUS).map(([k, v]) => (
                <Checkbox
                    key={"subjects-filter-checkbox-for-" + k}
                    label={v.label}
                    setValueAction={() => toggleStatus(k as SubjectStatus)}
                    defaultValue={DEFAULT_STATUS[k as SubjectStatus].default}
                />
            ))}
        </div>
    );
}
