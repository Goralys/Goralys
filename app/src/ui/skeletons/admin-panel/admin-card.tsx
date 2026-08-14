import { Card } from "@/app/src/ui/card";
import { ButtonSkeleton } from "@/app/src/ui/skeletons/button";
import { ReactElement } from "react";

export default function AdminPanelCardSkeleton(): ReactElement {
    return (
        <Card className="flex-col w-175! bg-sky-200 gap-1 p-1 mb-1 mt-1">
            <div className="flex flex-row justify-between items-center">
                <div className="flex flex-row">
                    <div className="skeleton h-7 w-7 rounded-xs mr-1.5" />
                    <div className="skeleton h-5 w-48 rounded-xs self-center" />
                </div>
                <div className="flex flex-row w-50 gap-1">
                    <ButtonSkeleton className="w-50!" />
                </div>
            </div>
        </Card>
    );
}
