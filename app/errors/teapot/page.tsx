import TeapotPageClient from "@/app/errors/teapot/teapot-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Erreur",
};
export default function TeapotErrorPage(): ReactElement {
    return <TeapotPageClient />;
}
