import MePageClient from "@/app/user/me/me-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Profil",
};
export default function ProfilePage(): ReactElement {
    return <MePageClient />;
}
