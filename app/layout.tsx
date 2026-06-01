import type { Metadata } from "next";
import "./globals.css";
import { SideNav } from "@/app/src/ui/nav/side-nav";
import { Lusitania } from "@/app/src/lib/fonts";
import React, { ReactElement } from "react";
import { AuthListener } from "@/app/src/lib/auth/auth-listener";
import FlashToastListener from "@/app/src/ui/toast/flash-toast-listener";
import { UserListener } from "@/app/src/lib/auth/user-listerner";
import { Providers } from "@/app/src/ui/modals/providers";
import CookieBanner from "@/app/src/ui/basic/cookies-banner";

export const metadata: Metadata = {
    title: "Goralys",
    description: "A cool project.",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>): ReactElement {
    return (
        <html lang="fr">
            <head>
                <title>Goralys</title>
            </head>

            <body className={`${Lusitania.className} antialiased text-gray-900 bg-gray-50 lg:overflow-auto overflow-hidden`}>
                <div className="z-50 absolute h-screen w-screen lg:hidden bg-gray-200 flex items-center justify-center p-6 overflow-hidden">
                    <h1 className="font-bold text-center text-2xl">
                        Goralys n&#39;est malheureusement pas disponible sur téléphone et tablette pour l&#39;instant.
                    </h1>
                </div>

                <Providers>
                    <AuthListener />
                    <UserListener />
                    <FlashToastListener />
                    <div className="flex flex-row min-h-screen">
                        <SideNav />
                        <main className="flex ml-55 w-full">
                            {children}
                            <CookieBanner />
                        </main>
                    </div>
                </Providers>
            </body>
        </html>
    );
}
