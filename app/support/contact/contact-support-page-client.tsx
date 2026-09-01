"use client";

import { ReactElement } from "react";
import { Card } from "@/app/src/ui/card";
import Image from "next/image";
import Link from "next/link";
import SupportForm from "@/app/src/ui/support/support-form";

export default function ContactSupportPageClient(): ReactElement {
    return (
        <div className="flex grow content-center justify-center items-center h-fit sm:p-10 p-0 min-h-screen">
            <div className="grid sm:w-5xl sm:ml-auto ml-2 sm:mr-auto mr-2 w-full gap-1 sm:grid-rows-1 sm:grid-cols-2 grid-rows-2 h-fit">
                <Card className="flex-row h-73 sm:h-full bg-sky-300 order-2 sm:order-1">
                    <Image
                        className="sticky top-0 h-auto sm:w-50 w-40"
                        src="/support/support.svg"
                        alt="Login illustration."
                        width={200}
                        height={150}
                    />

                    <div className="flex-col">
                        <h1 className="text-lg sm:text-xl">Vous avez besoin d&#39;aide ?</h1>
                        <p className="text-2xs">
                            Avant de contacter le support, il est recommandé de vérifier le{" "}
                            <Link className="text-sky-600 underline" href="/help">
                                centre d&#39;aide
                            </Link>{" "}
                            avant pour vérifier si vous n&#39;y trouvez pas votre réponse.
                        </p>
                    </div>
                </Card>
                <SupportForm />
            </div>
        </div>
    );
}
