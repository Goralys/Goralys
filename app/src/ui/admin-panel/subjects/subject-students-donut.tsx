import { SubjectStatus } from "@goralys/core";
import { ReactElement, useEffect, useState } from "react";

const STATUS_CONFIG: Record<SubjectStatus | "empty", { percent: number; color: string }> = {
    empty: { percent: 0, color: "#D4D4D8" }, // zinc-300
    not_submitted: { percent: 33, color: "#2563EB" }, // blue-600
    submitted: { percent: 66, color: "#D97706" }, // amber-600
    approved: { percent: 100, color: "#059669" }, // green-600
    rejected: { percent: 100, color: "#DC2626" }, // red-600
};

const TRACK_COLOR = "#D4D4D8"; // zinc-300

interface Props {
    status: SubjectStatus | "empty";
    size?: number;
}

export default function SubjectStudentsDonut({ status, size = 48 }: Props): ReactElement {
    const { percent, color } = STATUS_CONFIG[status];
    const strokeWidth = size * 0.15;
    const radius = (size - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;

    // 0% (offset = circumference) on mount
    const [offset, setOffset] = useState(circumference);

    useEffect(() => {
        // triggered after render for css animation
        const targetOffset = circumference - (percent / 100) * circumference;
        const raf = requestAnimationFrame(() => setOffset(targetOffset));
        return (): void => cancelAnimationFrame(raf);
    }, [percent, circumference]);

    return (
        <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
            <circle cx={size / 2} cy={size / 2} r={radius} fill="none" stroke={TRACK_COLOR} strokeWidth={strokeWidth} />
            <circle
                cx={size / 2}
                cy={size / 2}
                r={radius}
                fill="none"
                stroke={color}
                strokeWidth={strokeWidth}
                strokeDasharray={circumference}
                strokeDashoffset={offset}
                strokeLinecap="round"
                transform={`rotate(-90 ${size / 2} ${size / 2})`}
                style={{ transition: "stroke-dashoffset 0.6s ease-out" }}
            />
        </svg>
    );
}
