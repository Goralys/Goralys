import type { Metadata } from "next";
import { ReactElement } from "react";
import HelpPageClient from "@/app/help/help-page-client";

export const metadata: Metadata = {
    title: "Goralys | Centre d'Aide",
};
export default function HelpPage(): ReactElement {
    return <HelpPageClient />;
}
