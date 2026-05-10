"use client";

import Link from "next/link";
import Image from "next/image";
import { Card } from "@/app/ui/card";
import RegisterForm from "@/app/ui/user/forms/register-form";
import { ReactElement } from "react";

export default function RegisterPageClient(): ReactElement {
    return (
        <div className="flex grow content-center justify-center items-center">
            <div className="grid w-5xl gap-1 grid-cols-2">
                <Card className="flex-row h-79 bg-sky-300">
                    <Image src="/user/register.svg" alt="Login illustration." width={200} height={150} />

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
