import Link from "next/link";
import { ReactElement } from "react";

export const metadata: { title: string } = {
    title: "Goralys | Erreur",
};
export default function NotFound(): ReactElement {
    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2 mt-4 flex flex-col items-center gap-4">
                <p className="text-9xl font-bold text-sky-300">404</p>
                <p className="text-2xl underline">Vous êtes perdu ?</p>
                <p className="text-body">La page que vous cherchez n&apos;existe pas ou a été déplacée.</p>
                <Link href="/" className="mt-4 text-sky-500 underline hover:text-sky-700 transition-colors duration-200">
                    Retour à l&apos;accueil
                </Link>
            </div>
        </div>
    );
}
