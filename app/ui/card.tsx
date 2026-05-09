import { ReactElement, ReactNode } from "react";

export function Card({ children, className }: { children: ReactNode; className?: string }): ReactElement {
    return <div className={`flex w-full rounded-xs p-3 gap-3 ${className}`}>{children}</div>;
}
