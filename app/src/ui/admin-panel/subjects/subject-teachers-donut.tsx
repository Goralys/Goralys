import { SubjectStatus } from "@goralys/core";
import { ReactElement, useEffect, useState } from "react";

const STATUS_CONFIG: Record<SubjectStatus | "empty", { color: string }> = {
    empty: { color: "#D4D4D8" }, // zinc-300
    not_submitted: { color: "#2563EB" }, // blue-600
    submitted: { color: "#D97706" }, // amber-600
    approved: { color: "#059669" }, // green-600
    rejected: { color: "#DC2626" }, // red-600
};

const TRACK_COLOR = "#D4D4D8"; // zinc-300

interface Props {
    size?: number;
    total: number;
    notSubmitted: number;
    submitted: number;
    rejected: number;
    approved: number;
}

interface CircleConfig {
    status: SubjectStatus;
    color: string;
    percent: number;
    offset: number;
}

export default function SubjectTeachersDonut({ size = 192, total, notSubmitted, submitted, rejected, approved }: Props): ReactElement {
    const strokeWidth = size * 0.15;
    const radius = (size - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;

    const approvedPercent = approved / total;
    const rejectedPercent = rejected / total;
    const submittedPercent = submitted / total;
    const notSubmittedPercent = notSubmitted / total;

    // 0% (offset = circumference) on mount
    const [notSubmittedOffset, setNotSubmittedOffset] = useState(circumference);
    const [submittedOffset, setSubmittedOffset] = useState(circumference);
    const [rejectedOffset, setRejectedOffset] = useState(circumference);
    const [approvedOffset, setApprovedOffset] = useState(circumference);

    useEffect(() => {
        // triggered after render for css animation
        const raf = requestAnimationFrame(() => {
            let sum = approvedPercent;
            setApprovedOffset(circumference - sum * circumference);
            sum += rejectedPercent;
            setRejectedOffset(circumference - sum * circumference);
            sum += submittedPercent;
            setSubmittedOffset(circumference - sum * circumference);
            setNotSubmittedOffset(0);
        });
        return (): void => cancelAnimationFrame(raf);
    }, [circumference, approvedPercent, rejectedPercent, submittedPercent]);

    const circles: CircleConfig[] = [
        { status: "not_submitted", color: STATUS_CONFIG["not_submitted"].color, percent: notSubmittedPercent, offset: notSubmittedOffset },
        { status: "submitted", color: STATUS_CONFIG["submitted"].color, percent: submittedPercent, offset: submittedOffset },
        { status: "rejected", color: STATUS_CONFIG["rejected"].color, percent: rejectedPercent, offset: rejectedOffset },
        { status: "approved", color: STATUS_CONFIG["approved"].color, percent: approvedPercent, offset: approvedOffset },
    ];

    let delay = circles.filter((c) => c.percent > 0).length;
    const ANIM_DURATION = 0.6;

    return (
        <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} className="mr-2">
            <circle cx={size / 2} cy={size / 2} r={radius} fill="none" stroke={TRACK_COLOR} strokeWidth={strokeWidth} />

            {circles.map(
                (c) =>
                    c.percent > 0 && (
                        <circle
                            key={c.status}
                            cx={size / 2}
                            cy={size / 2}
                            r={radius}
                            fill="none"
                            stroke={c.color}
                            strokeWidth={strokeWidth}
                            strokeDasharray={circumference}
                            strokeDashoffset={c.offset}
                            strokeLinecap="butt"
                            transform={`rotate(-90 ${size / 2} ${size / 2})`}
                            style={{ transition: `stroke-dashoffset ${ANIM_DURATION}s ease-out ${ANIM_DURATION * delay--}s` }}
                        />
                    ),
            )}
        </svg>
    );
}
