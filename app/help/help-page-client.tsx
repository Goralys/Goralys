"use client";

import { useState, ReactElement } from "react";
import { clsx } from "clsx";
import { ChevronRightIcon, QuestionMarkCircleIcon } from "@heroicons/react/24/outline";
import { helpContent } from "@/app/lib/config";
import Link from "next/link";

export default function HelpPage(): ReactElement {
    const [activeSection, setActiveSection] = useState<string>("getting-started");
    const [expandedSubsection, setExpandedSubsection] = useState<string | null>(null);

    const current = helpContent.find((s) => s.id === activeSection) ?? helpContent[0];

    return (
        <>
            <div className="relative flex grow h-fit min-h-screen">
                {/* Sidebar */}
                <aside className="w-51 shrink-0 bg-transparent pt-6 px-3">
                    <div className="flex flex-row items-center justify-start mb-9">
                        <QuestionMarkCircleIcon className="size-5 mr-1" />
                        <p className="font-medium text-xl">Centre d&#39;aide</p>
                    </div>
                    <details className="group/navdetails">
                        <summary className="cursor-pointer flex flex-row">
                            <ChevronRightIcon className="size-3 group-open/navdetails:rotate-90 transition-all duration-200" />
                            <p className="text-xs text-gray-900 uppercase font-semibold px-3 mb-3">Rubriques</p>
                        </summary>
                        <nav className="flex flex-col gap-1">
                            {helpContent.map((section) => (
                                <button
                                    key={section.id}
                                    onClick={() => {
                                        setActiveSection(section.id);
                                        setExpandedSubsection(null);
                                    }}
                                    className={clsx(
                                        "hover:bg-sky-200 h-10 w-full items-center flex flex-row hover:text-sky-600 left-1 rounded-md " +
                                            "transition-colors p-2 text-sm text-left",
                                        {
                                            "bg-sky-200 text-sky-600": activeSection === section.id,
                                            "bg-gray-100 text-gray-900": activeSection !== section.id,
                                        },
                                    )}
                                >
                                    {section.title}
                                </button>
                            ))}
                        </nav>
                    </details>
                </aside>

                {/* Main content */}
                <main className="flex-1 px-10 py-8 flex flex-col items-center">
                    <div className="flex flex-col gap-3 w-full max-w-3xl">
                        <p className="self-start underline text-xl font-semibold text-gray-800 mb-6">{current.title} :</p>
                        {current.subsections.map((sub) => (
                            <div key={sub.id} className="bg-sky-100 rounded-md overflow-hidden">
                                <button
                                    onClick={() => setExpandedSubsection(expandedSubsection === sub.id ? null : sub.id)}
                                    className="w-full flex items-center justify-between px-4 py-3 hover:bg-sky-200 transition text-left"
                                >
                                    <span className="font-medium text-gray-800">{sub.title}</span>
                                    <ChevronRightIcon
                                        className={clsx("w-5 h-5 text-gray-600 transition-transform duration-200", {
                                            "rotate-90": expandedSubsection == sub.id,
                                        })}
                                    />
                                </button>

                                {expandedSubsection === sub.id && (
                                    <div className="px-4 pb-4 pt-2 flex flex-col gap-4 bg-sky-50 border-t border-sky-200">
                                        {sub.items.map((item, i) => (
                                            <div key={i}>
                                                <p className="font-medium text-gray-800 text-sm mb-1">{item.q}</p>
                                                <p className="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{item.a}</p>
                                            </div>
                                        ))}

                                        {sub.details && (
                                            <div className="flex flex-col gap-3">
                                                {sub.details.map((detail, i) => (
                                                    <div key={i} className="bg-white rounded border border-sky-200 p-3">
                                                        <p className="font-semibold text-gray-800 text-sm mb-1">{detail.title}</p>
                                                        <p className="text-gray-500 text-xs mb-2">{detail.description}</p>
                                                        <pre className="bg-sky-100 text-gray-700 text-xs p-3 rounded font-mono overflow-x-auto">
                                                            {detail.format}
                                                        </pre>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </main>
            </div>

            <footer className="fixed bottom-0 flex flex-col left-55 right-1 h-15 items-center">
                <div className="h-px w-full bg-gray-300 top-0"></div>
                <p className="mb-5 mt-2">
                    Contactez le{" "}
                    <Link className="text-sky-600 underline hover:text-sky-800 transition-colors duration-200" href="/support">
                        support
                    </Link>{" "}
                </p>
            </footer>
        </>
    );
}
