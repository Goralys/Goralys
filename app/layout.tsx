import type { Metadata } from "next";
import "./globals.css";
import { Lusitania } from "@/app/src/lib/fonts";
import React, { ReactElement } from "react";
import { AuthListener } from "@/app/src/lib/auth/auth-listener";
import FlashToastListener from "@/app/src/ui/toast/flash-toast-listener";
import { UserListener } from "@/app/src/lib/auth/user-listerner";
import { Providers } from "@/app/src/ui/modals/providers";
import CookieBanner from "@/app/src/ui/basic/cookies-banner";
import { NavigationListener } from "@/app/src/lib/navigation/navigation-listener";
import Bootstrap from "@/app/src/ui/bootstrap";
import DynamicNav from "@/app/src/ui/nav/dynamic-nav";

export const metadata: Metadata = {
    title: "Goralys",
    description: "Un site pour simplifier la gestion du Grand Oral en Terminale.",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>): ReactElement {
    return (
        <html lang="fr">
            <head>
                <title>Goralys</title>
            </head>

            <body className={`${Lusitania.className} antialiased text-gray-900 bg-gray-50 overflow-auto`}>
                {/*
                <div className="z-50 absolute h-screen w-screen lg:hidden bg-gray-200 flex items-center justify-center p-6 overflow-hidden">
                    <h1 className="font-bold text-center text-2xl">
                        Goralys n&#39;est malheureusement pas disponible sur téléphone et tablette pour l&#39;instant.
                    </h1>
                </div>
                 */}

                <Bootstrap />

                <Providers>
                    <AuthListener />
                    <UserListener />
                    <NavigationListener />
                    <FlashToastListener />
                    <div className="flex flex-col-reverse sm:flex-row min-h-screen">
                        <DynamicNav />
                        <main className="flex pb-12 sm:pb-0 sm:ml-55 w-full">
                            {children}
                            <CookieBanner />
                        </main>
                    </div>
                </Providers>
            </body>
        </html>
    );
}
