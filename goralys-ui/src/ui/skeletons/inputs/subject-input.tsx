import { ReactElement } from "react";

interface Props {
    showMeta?: boolean;
}

export function SubjectInputSkeleton({ showMeta = true }: Props): ReactElement {
    return (
        <div className="relative mt-3 min-w-50 mb-5">
            <div className="skeleton w-full h-10 rounded-xs" />
            {showMeta && (
                <div className="flex flex-row justify-between mt-1.5">
                    <div className="skeleton h-3 w-24 rounded-xs" />
                    <div className="skeleton h-4 w-32 rounded-xs" />
                </div>
            )}
        </div>
    );
}
