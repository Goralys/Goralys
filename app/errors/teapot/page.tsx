import TeapotPageClient from "@/app/errors/teapot/teapot-page-client";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Erreur",
};
export default function TeapotErrorPage(): ReactElement {
    return <TeapotPageClient />;
}
