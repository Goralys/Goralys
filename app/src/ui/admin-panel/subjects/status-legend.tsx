import { ReactElement } from "react";

const STATUS_LABELS: Record<"not_submitted" | "submitted" | "approved" | "rejected", { label: string; color: string }> = {
    not_submitted: { label: "Brouillon", color: "#2563EB" },
    submitted: { label: "Envoyée", color: "#D97706" },
    approved: { label: "Validée", color: "#059669" },
    rejected: { label: "Rejetée", color: "#DC2626" },
};

interface Props {
    total: number;
    submitted: number;
    approved: number;
    rejected: number;
    notSubmitted: number;
}

export default function StatusLegend({ total, submitted, approved, rejected, notSubmitted }: Props): ReactElement {
    const counts = {
        not_submitted: notSubmitted,
        submitted,
        approved,
        rejected,
    };

    return (
        <ul className="flex flex-col gap-1 self-center mr-10">
            {(Object.keys(STATUS_LABELS) as (keyof typeof STATUS_LABELS)[]).map((key) => (
                <li key={key} className="flex items-center gap-2 text-sm">
                    <span
                        className="inline-block w-2.5 h-2.5 rounded-full shrink-0"
                        style={{ backgroundColor: STATUS_LABELS[key].color }}
                    />
                    <span>{STATUS_LABELS[key].label}</span>
                    <span className="text-gray-500 ml-auto">
                        {counts[key]} / {total}
                    </span>
                </li>
            ))}
        </ul>
    );
}
