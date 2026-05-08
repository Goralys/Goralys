import { TeacherCardSkeletonProps } from "@/app/lib/types";
import { SubjectInputSkeleton } from "@/app/ui/skeletons/inputs/subject-input";
import { CommentSkeleton } from "@/app/ui/skeletons/subjects/comment";
import { ButtonSkeleton } from "@/app/ui/skeletons/button";

export default function TeacherCardSkeleton({ showButtons = false }: TeacherCardSkeletonProps) {
    return (
        <div className="h-fit w-200 flex flex-col bg-sky-200 gap-1 p-1 mb-1 mt-1">
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