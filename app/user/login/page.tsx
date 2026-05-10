import { ReactElement, Suspense } from "react";
import LoginPageClient from "@/app/user/login/login-page-client";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Connexion",
};
export default function LoginPage(): ReactElement {
    return (
        <Suspense fallback={null}>
            <LoginPageClient />
        </Suspense>
    );
}
