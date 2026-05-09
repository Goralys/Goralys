import RegisterPageClient from "@/app/user/register/register-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Enregistrement",
};
export default function RegisterPage(): ReactElement {
    return <RegisterPageClient />;
}
