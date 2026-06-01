import { SubjectInputSkeleton } from "@/app/src/ui/skeletons/inputs/subject-input";
import { ReactElement } from "react";

export default function AdminSubjectCardSkeleton(): ReactElement {
    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <div className="skeleton h-5 w-24 rounded-xs" />
                <div className="skeleton h-5 w-40 rounded-xs" />
            </div>
            <SubjectInputSkeleton showMeta={false} />
        </div>
    );
}
