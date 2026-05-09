import TeapotPageClient from "@/app/errors/teapot/teapot-page-client";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Questions",
};
export default function TeacherPage(): ReactElement {
    return <TeapotPageClient />;
}
