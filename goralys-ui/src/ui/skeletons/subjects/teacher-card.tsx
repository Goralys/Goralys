import { SubjectInputSkeleton } from "#lib/ui/skeletons/inputs/subject-input";
import { CommentSkeleton } from "#lib/ui/skeletons/subjects/comment";
import { ButtonSkeleton } from "#lib/ui/skeletons/button";
import { ReactElement } from "react";

interface Props {
    showButtons?: boolean;
}

export function TeacherCardSkeleton({ showButtons = false }: Props): ReactElement {
    return (
        <div className="h-fit sm:w-200 w-96 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row w-full justify-between">
                <div className="skeleton h-5 w-24 rounded-xs" />
                <div className="skeleton h-5 w-24 rounded-xs" />
            </div>
            <SubjectInputSkeleton />
            <CommentSkeleton />
            {showButtons && (
                <>
                    <ButtonSkeleton className="-mb-1! mt-1!" />
                    <ButtonSkeleton className="mb-1! mt-1!" />
                </>
            )}
        </div>
    );
}
