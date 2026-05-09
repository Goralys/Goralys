import { ReactElement, Suspense } from "react";
import LoginPageClient from "@/app/user/login/login-page-client";

export const metadata: { title: string } = {
    title: "Goralys | Connexion",
};
export default function LoginPage(): ReactElement {
    return (
        <Suspense fallback={null}>
            <LoginPageClient />
        </Suspense>
    );
}
