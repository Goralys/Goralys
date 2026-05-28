import { ReactElement } from "react";
import ContactSupportPageClient from "@/app/support/contact/contact-support-page-client";
import type { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Support",
};
export default function ContactSupportPage(): ReactElement {
    return <ContactSupportPageClient />;
}
