"use client";

import Link from "next/link";
import { ReactElement, useState } from "react";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { Button } from "@/app/ui/button";
import { useToast } from "@/app/ui/toast/toast-provider";

const flavours = [
    { value: "chocolate", label: "Chocolat" },
    { value: "vanilla", label: "Vanille" },
    { value: "raisin", label: "Raisin" },
    { value: "caramel", label: "Caramel" },
];

export default function CookiesPageClient(): ReactElement {
    const { showToast } = useToast();
    const [flavour, setFlavour] = useState("chocolate");

    const orderCookie = async (): Promise<void> => {
        const payload = { flavour: flavour };

        const res = await goralysFetchClient(`cookies`, {
            method: "POST",
            headers: { "X-HTTP-Method-Override": "BREW" },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (data?.toast) {
            showToast({
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            });

            window.location.href = data?.redirect ?? "/";
        }
    };

    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2 mt-4 mb-16 flex flex-col items-center gap-4 max-w-2xl">
                <p className="text-4xl font-bold text-sky-300">Politique de cookies</p>
                <p className="text-sm text-gray-400">Dernière mise à jour : mai 2026</p>

                <div className="flex flex-col gap-8 text-body w-full">
                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">1. Qu&apos;est-ce qu&apos;un cookie ?</p>
                        <p>
                            Un cookie est un petit fichier texte déposé sur votre navigateur lors de votre visite sur Goralys. Il permet au
                            site de mémoriser des informations essentielles entre vos visites, comme votre session de connexion ou votre
                            progression dans la préparation de votre Grand Oral.
                        </p>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">2. Les cookies utilisés</p>
                        <p>
                            Goralys n&apos;utilise que des cookies <strong>strictement nécessaires</strong> au fonctionnement du service.
                            Aucun cookie publicitaire, analytique ou de traçage tiers n&apos;est déposé. Nous ne cherchons pas à collecter
                            vos habitudes de navigation, votre localisation ou toute autre donnée sans lien avec le service.
                        </p>
                        <table className="w-full text-sm border-collapse mt-2">
                            <thead>
                                <tr className="border-b border-gray-300 text-left">
                                    <th className="py-2 pr-4">Nom</th>
                                    <th className="py-2 pr-4">Rôle</th>
                                    <th className="py-2 pr-4">Durée</th>
                                    <th className="py-2">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">GORALYSSESSID</td>
                                    <td className="py-2 pr-4">Maintien de votre session de connexion.</td>
                                    <td className="py-2 pr-4">1 heure</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">public-id</td>
                                    <td className="py-2 pr-4">
                                        Identifiant public anonyme, ne permet pas de vous identifier personnellement.
                                    </td>
                                    <td className="py-2 pr-4">1 heure</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">username</td>
                                    <td className="py-2 pr-4">Votre identifiant de connexion.</td>
                                    <td className="py-2 pr-4">1 heure</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">user-role</td>
                                    <td className="py-2 pr-4">Votre rôle sur la plateforme (élève, enseignant, administrateur).</td>
                                    <td className="py-2 pr-4">1 heure</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">subjects-synced-*</td>
                                    <td className="py-2 pr-4">Indique si vos sujets ont été synchronisés, évite des requêtes inutiles.</td>
                                    <td className="py-2 pr-4">Session</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">users-synced</td>
                                    <td className="py-2 pr-4">
                                        Indique si la liste des utilisateurs a été synchronisée (administrateurs uniquement).
                                    </td>
                                    <td className="py-2 pr-4">Session</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                                <tr className="border-b border-gray-100">
                                    <td className="py-2 pr-4 font-mono text-sky-400">virtual-users-synced</td>
                                    <td className="py-2 pr-4">
                                        Indique si les utilisateurs virtuels ont été synchronisés (administrateurs uniquement).
                                    </td>
                                    <td className="py-2 pr-4">Session</td>
                                    <td className="py-2">Fonctionnel</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">3. Données personnelles stockées</p>
                        <p>
                            Dans le cadre de l&apos;utilisation de Goralys, certaines données vous concernant sont enregistrées sur nos
                            serveurs afin de faire fonctionner le service. Ces données se limitent strictement à :
                        </p>
                        <ul className="flex flex-col gap-1 mt-1 list-disc list-inside">
                            <li>
                                Votre <strong>nom et prénom</strong>, utilisés pour vous identifier sur la plateforme.
                            </li>
                            <li>
                                Vos <strong>sujets de Grand Oral</strong>, enregistrés pour vous permettre de les retrouver d&apos;une
                                session à l&apos;autre.
                            </li>
                            <li>
                                Votre <strong>identifiant de connexion</strong> et votre <strong>rôle</strong> sur la plateforme (élève,
                                enseignant, administrateur).
                            </li>
                        </ul>
                        <p className="mt-1">
                            Aucune autre information n&apos;est collectée ou conservée : ni adresse e-mail, ni numéro de téléphone, ni
                            donnée de navigation, ni information financière. Goralys ne revend et ne partage aucune de ces données avec des
                            tiers.
                        </p>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">4. Consentement</p>
                        <p>
                            Conformément à l&apos;article 82 de la loi Informatique et Libertés et aux lignes directrices de la CNIL, les
                            cookies strictement nécessaires au fonctionnement d&apos;un service sont exemptés de consentement. Goralys ne
                            déposant rien au-delà de ce qui est indispensable, aucune bannière de consentement n&apos;est affichée.
                        </p>
                        <p>
                            Si vous êtes mineur, l&apos;utilisation de Goralys se fait dans un cadre scolaire encadré par votre
                            établissement. Aucune donnée supplémentaire à celles listées ci-dessus n&apos;est collectée vous concernant.
                        </p>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">5. Durée de conservation</p>
                        <p>
                            Les cookies de session expirent automatiquement après <strong>1 heure d&apos;inactivité</strong>. Les données
                            enregistrées côté serveur (nom, prénom, sujets) sont quant à elles conservées le temps de votre utilisation de
                            la plateforme et supprimées à la fin de l&apos;année scolaire ou sur demande de l&apos;administrateur de votre
                            établissement.
                        </p>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">6. Gérer les cookies</p>
                        <p>
                            Vous pouvez supprimer ou bloquer les cookies via les paramètres de votre navigateur. Notez que désactiver le
                            cookie de session empêchera toute connexion à la plateforme.
                        </p>
                        <ul className="flex flex-col gap-1 mt-1">
                            <li>
                                <Link
                                    href="https://support.google.com/chrome/answer/95647"
                                    target="_blank"
                                    rel="noopener"
                                    className="text-sky-500 underline hover:text-sky-700 transition-colors duration-200"
                                >
                                    Google Chrome
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="https://support.mozilla.org/fr/kb/activer-desactiver-cookies"
                                    target="_blank"
                                    rel="noopener"
                                    className="text-sky-500 underline hover:text-sky-700 transition-colors duration-200"
                                >
                                    Mozilla Firefox
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac"
                                    target="_blank"
                                    rel="noopener"
                                    className="text-sky-500 underline hover:text-sky-700 transition-colors duration-200"
                                >
                                    Safari
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="https://support.microsoft.com/fr-fr/help/4468242"
                                    target="_blank"
                                    rel="noopener"
                                    className="text-sky-500 underline hover:text-sky-700 transition-colors duration-200"
                                >
                                    Microsoft Edge
                                </Link>
                            </li>
                        </ul>
                    </section>

                    <section className="flex flex-col gap-2">
                        <p className="text-xl underline">7. Un cookie, justement ?</p>
                        <p>
                            Maintenant que vous savez tout sur nos cookies, on se dit qu&apos;on vous en doit bien un vrai. Quel goût vous
                            ferait plaisir ?
                        </p>
                        <div className="flex flex-row gap-3 items-center mt-2">
                            <select
                                value={flavour}
                                onChange={(e) => setFlavour(e.target.value)}
                                className="border-0 border-b-2 border-sky-300 appearance-none
                                cursor-pointer outline-none focus:ring-0 text-base leading-5
                                text-heading pb-0 pr-5 subjects-search-select"
                            >
                                {flavours.map((f) => (
                                    <option key={f.value} value={f.value}>
                                        {f.label}
                                    </option>
                                ))}
                            </select>
                            <Button text="Commander" type="button" onClick={orderCookie} className="h-10! w-50! ml-5" />
                        </div>
                    </section>
                </div>

                <Link href="/" className="mt-4 text-sky-500 underline hover:text-sky-700 transition-colors duration-200">
                    Retour à l&apos;accueil
                </Link>
            </div>
        </div>
    );
}
