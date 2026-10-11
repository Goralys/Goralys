import { GoralysLogoLoader } from "@goralys/ui";
import { ReactElement } from "react";

export default function Loading(): ReactElement {
    return (
        <div className="flex items-center justify-center min-h-screen min-w-full">
            <GoralysLogoLoader />
        </div>
    );
}
