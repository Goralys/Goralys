"use client";

import Link from "next/link";
import Image from "next/image";
import { Card } from "@/app/src/ui/card";
import RegisterForm from "@/app/src/ui/user/forms/register-form";
import { ReactElement } from "react";

export default function RegisterPageClient(): ReactElement {
    return (
        <div className="flex grow content-center justify-center items-center min-h-screen">
            <div className="grid sm:w-5xl sm:ml-auto ml-2 sm:mr-auto mr-2 w-full gap-1 sm:grid-cols-2 grid-rows-2">
                <Card className="flex-row sm:h-79 h-fit p-1 bg-sky-300 order-2 sm:order-1">
                    <Image className="h-auto sm:w-50 w-40" src="/user/register.svg" alt="Login illustration." width={200} height={150} />

                    <div className="flex flex-col">
                        <h1 className="text-xl">Bienvenue chez Goralys !</h1>
                        <p className="text-2xs">
                            Créer votre compte pour retrouver toutes vos questions en un seul endroit. Vous avez déjà un compte ? Rendez
                            vous sur la
                            <Link className="text-sky-600 underline" href="/user/login">
                                {" "}
                                page de connexion
                            </Link>
                        </p>
                    </div>
                </Card>

                <RegisterForm />
            </div>
        </div>
    );
}
