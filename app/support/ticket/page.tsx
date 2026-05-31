import { ReactElement } from "react";
import SupportTicketPageClient from "./support-ticket-page-client";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";

interface PageParams {
    t: string;
}

export default async function Page({ searchParams }: { searchParams: Promise<PageParams> }): Promise<ReactElement> {
    const id = (await searchParams).t;

    if (!id) redirect("/support");

    const cookieStore = await cookies();
    const sessionCookie = cookieStore.get("GORALYSSESSID")?.value;

    const csrfRes = await fetch(`${process.env.NEXT_PUBLIC_API_DOMAIN}/csrf`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            cookie: sessionCookie ? `GORALYSSESSID=${sessionCookie}` : "",
        },
        body: JSON.stringify({ form: "get-ticket" }),
    });

    if (!csrfRes.ok) {
        redirect("/login");
    }

    const csrfData = await csrfRes.json();
    const csrfToken = csrfData["csrf-token"];

    const res = await fetch(`${process.env.NEXT_PUBLIC_API_DOMAIN}/support/ticket?t=${id}&csrf-token=${csrfToken}`, {
        headers: {
            cookie: sessionCookie ? `GORALYSSESSID=${sessionCookie}` : "", // ← PASSE LA SESSION
        },
    });

    const text = await res.text();

    if (res.status === 401) redirect("/user/login?reason=unauthenticated");
    if (res.status !== 200) redirect("/support");
    if (text.startsWith("<")) {
        console.error("Backend returned HTML instead of JSON!");
        redirect("/support");
    }

    const ticket = JSON.parse(text);

    return <SupportTicketPageClient ticket={ticket} />;
}
