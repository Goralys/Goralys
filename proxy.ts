import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import { SubjectsProxy } from "./app/lib/proxies/subjects-proxy";
import { AdminsProxy } from "./app/lib/proxies/admins-proxy";

const routes: Array<{
    matcher: RegExp;
    handler: (req: NextRequest) => Promise<NextResponse>;
}> = [
    { matcher: /^\/subject/, handler: SubjectsProxy },
    { matcher: /^\/admin/, handler: AdminsProxy },
];

export async function proxy(request: NextRequest): Promise<NextResponse | NextResponse<unknown>> {
    const { pathname } = request.nextUrl;

    for (const route of routes) {
        if (route.matcher.test(pathname)) {
            return route.handler(request);
        }
    }

    return NextResponse.next();
}

export const config = {
    matcher: ["/subject/:path*", "/admin/:path*"],
};
