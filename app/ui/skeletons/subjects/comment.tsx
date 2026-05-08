export function CommentSkeleton() {
    return (
        <div className="flex flex-row items-center gap-1.5">
            <div className="skeleton h-4 w-4 rounded-xs" />
            <div className="skeleton h-4 w-44 rounded-xs" />
        </div>
    );
}