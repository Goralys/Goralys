import { ReactElement } from "react";
import { Metadata } from "next";
import NotFoundClient from "@/app/not-found-client";

export const metadata: Metadata = {
    title: "Goralys | Erreur",
};
export default function NotFound(): ReactElement {
    return <NotFoundClient />;
}
