import { ReactElement } from "react";
import UserPanelPageClient from "@/app/admin/user/user-panel-page-client";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { UserProfile } from "@goralys/core";

interface PageParams {
    u: string;
}

export default async function Page({ searchParams }: { searchParams: Promise<PageParams> }): Promise<ReactElement> {
    const pubId = (await searchParams).u;

    if (!pubId) redirect("/admin/users");

    const cookieStore = await cookies();
    const sessionCookie = cookieStore.get("GORALYSSESSID")?.value;

    const csrfRes = await fetch(`${process.env.NEXT_PUBLIC_API_DOMAIN}/csrf`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            cookie: sessionCookie ? `GORALYSSESSID=${sessionCookie}` : "",
            "X-High-School-Token": process.env.NEXT_PUBLIC_API_TOKEN ?? "",
        },
        body: JSON.stringify({ form: "get-user-profile" }),
    });

    if (!csrfRes.ok) {
        redirect("/user/login?reason=unauthenticated");
    }

    const csrfData = await csrfRes.json();
    const csrfToken = csrfData["csrf-token"];

    const res = await fetch(`${process.env.NEXT_PUBLIC_API_DOMAIN}/users/profile/any?target=${pubId}&csrf-token=${csrfToken}`, {
        method: "GET",
        headers: {
            cookie: sessionCookie ? `GORALYSSESSID=${sessionCookie}` : "",
            "X-High-School-Token": process.env.NEXT_PUBLIC_API_TOKEN ?? "",
        },
    });

    const text = await res.text();

    if (res.status === 401) redirect("/user/login?reason=unauthenticated");
    if (res.status !== 200) redirect("/admin/users");
    if (text.startsWith("<")) {
        console.error("Backend returned HTML instead of JSON!");
        redirect("/admin/users");
    }

    const profile: UserProfile = JSON.parse(text);

    return <UserPanelPageClient profile={profile} />;
}
