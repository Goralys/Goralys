"use client";

import Link from "next/link";
import Image from "next/image";
import { Card } from "@/app/src/ui/card";
import LoginForm from "@/app/src/ui/user/forms/login-form";
import { ReactElement, useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { emitUserEvent } from "@goralys/core";

export default function LoginPageClient(): ReactElement {
    const searchParams = useSearchParams();
    const { showToast } = useToast();
    const router = useRouter();

    useEffect(() => {
        const reason = searchParams.get("reason");
        console.log(reason);
        if (!reason) return;

        if (reason === "expired") {
            showToast({
                type: "warning",
                title: "Session",
                message: "Votre session a expirée, vous avez été déconnecté.",
            });
        } else if (reason === "unauthenticated") {
            showToast({
                type: "info",
                title: "Connexion",
                message: "Veuillez vous connecter pour accéder à votre espace",
            });
        } else if (reason === "unauthorized") {
            showToast({
                type: "warning",
                title: "Permission",
                message: "Vous n'avez pas les permissions nécessaires sur ce compte",
            });
            return;
        }

        emitUserEvent("logout");

        router.replace("/user/login");
    }, [searchParams, router, showToast]); // The toast dependency is ignored to avoid render loop.

    return (
        <div className="flex grow content-center justify-center items-center min-h-screen">
            <div className="grid sm:w-5xl sm:ml-auto ml-2 sm:mr-auto mr-2 w-full gap-1 sm:grid-cols-2 grid-rows-2">
                <Card className="flex-col sm:h-65 h-fit p-1 bg-sky-300 order-2 sm:order-1">
                    <Image className="h-auto sm:w-50 w-40" src="/user/login.svg" alt="Login illustration." width={200} height={150} />

                    <h1 className="text-xl">Bon retour chez Goralys !</h1>
                    <p className="text-2xs">
                        Reprenez vos questions là vous les avez laissées. Vous n&apos;avez pas encore de compte ? Pas de panique, créez-en
                        un sur la
                        <Link className="text-sky-600 underline" href="/user/register">
                            {" "}
                            page d&apos;enregistrement
                        </Link>
                    </p>
                </Card>
                <LoginForm />
            </div>
        </div>
    );
}
