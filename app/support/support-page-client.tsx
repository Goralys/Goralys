"use client";

import { ReactElement } from "react";
import { Card } from "@/app/ui/card";
import Image from "next/image";
import Link from "next/link";
import SupportForm from "@/app/ui/support/support-form";

export default function SupportPageClient(): ReactElement {
    return (
        <div className="flex grow content-center justify-center items-center h-fit p-10">
            <div className="grid w-5xl gap-1 grid-cols-2">
                <Card className="flex-row h-65 bg-sky-300 ">
                    <Image className="sticky top-0" src="/support/support.svg" alt="Login illustration." width={200} height={150} />

                    <div className="flex-col">
                        <h1 className="text-xl">Vous avez besoin d&#39;aide ?</h1>
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
