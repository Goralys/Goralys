import RegisterPageClient from "@/app/user/register/register-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Enregistrement",
};
export default function RegisterPage(): ReactElement {
    return <RegisterPageClient />;
}
