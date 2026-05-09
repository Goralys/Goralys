import CookiesPageClient from "@/app/cookies/cookies-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Cookies",
};
export default function CookiesPage(): ReactElement {
    return <CookiesPageClient />;
}
