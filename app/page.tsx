/*
 * Goralys — application de gestion des sujets du Grand oral
 * Copyright (C) 2025-2026 Sami Saubion
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

import Image from "next/image";
import { ReactElement } from "react";
import { Metadata } from "next";

export const metadata: Metadata = {
    title: "Goralys | Accueil",
};

export default function HomePage(): ReactElement {
    return (
        <div className="flex flex-col min-h-screen w-full">
            <div className="self-center w-3/4 mt-11 flex flex-col-reverse landing-page:flex-row landing-page:gap-10 landing-page:items-start">
                <div className="landing-page:order-2 w-150 landing-page:w-1/2">
                    <Image
                        src="/affiche/affiche.png"
                        alt="Affiche du Grand Oral"
                        width={600}
                        height={800}
                        className="rounded-md shadow-md h-auto max-h-screen mt-5 landing-page:mt-0"
                    />
                </div>

                <div className="landing-page:order-1 landing-page:w-1/2">
                    <h1 className="font-bold text-5xl mb-5">Bienvenue sur Goralys,</h1>
                    <p className="mb-2.5">L&#39;application de gestion du Grand Oral au lycée Auguste et Jean Renoir.</p>
                    <p>Cette plateforme a été entièrement développée par Sami Saubion, élève du lycée.</p>
                </div>
            </div>

            <div className="flex grow" />

            <footer className="flex flex-col items-start gap-2 mb-4 ml-4">
                <Image
                    src="/logo/logo_renoir.png"
                    alt="Logo du lycée Auguste et Jean Renoir"
                    width={75}
                    height={10}
                    className="h-auto w-auto"
                />
                <p className="text-xs opacity-70 self-center">
                    © 2026 Sami Saubion — AGPL-3.0 —
                    <a href="https://github.com/SAMSAM-55/Goralys" className="underline ml-1" target="_blank" rel="noopener noreferrer">
                        Source code
                    </a>
                </p>
            </footer>
        </div>
    );
}
