import { ReactElement } from "react";

export function ButtonSkeleton({ className }: { className?: string }): ReactElement {
    return <div className={`skeleton w-full h-10 mt-2 mb-2 rounded-xs ${className ?? ""}`} />;
}
