import CookiesPageClient from "@/app/cookies/cookies-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Cookies",
};
export default function CookiesPage(): ReactElement {
    return <CookiesPageClient />;
}
