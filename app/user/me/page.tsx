import MePageClient from "@/app/user/me/me-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Profil",
};
export default function ProfilePage(): ReactElement {
    return <MePageClient />;
}
