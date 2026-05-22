import { ReactElement } from "react";
import SupportPageClient from "@/app/support/support-page-client";
import type { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Support",
};
export default function SupportPage(): ReactElement {
    return <SupportPageClient />;
}
