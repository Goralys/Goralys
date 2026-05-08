'use client';

import Link from "next/link";
import GoralysLogoLoader from "@/app/ui/logo/goralys-logo-loader";
import {useRouter, useSearchParams} from "next/navigation";
import {useToast} from "@/app/ui/toast/toast-provider";
import {useEffect} from "react";

export default function Page() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const {showToast} = useToast();
    
    useEffect(() => {
        const raw = searchParams.get('toast');
        if (!raw) return;
        const data = JSON.parse(decodeURIComponent(raw));
        showToast({
            type: data.toastType,
            title: data.toastTitle,
            message: data.toastMessage,
        });
        router.replace('/errors/teapot');
    }, [router, searchParams, showToast]);
    
    return (
        <div className="relative flex flex-col grow h-fit items-center top-10">
            <div className="h-auto w-fit p-2 mt-4 flex flex-col items-center gap-4">
                <p className="text-9xl font-bold text-sky-300">418</p>
                <p className="text-2xl underline">Je suis une théière</p>
                <p className="text-body text-center max-w-xl">
                    Je ne peux malheureusement pas vous proposer de café, ni de vrai thé d&apos;ailleurs.
                    En revanche, vous pouvez admirer ce caméléon. Bien sûr, si vous en avez marre, vous pouvez aussi
                    <Link href="/" className="ml-0.5 text-sky-500 underline hover:text-sky-700 transition-colors duration-200">
                        retourner à l&apos;accueil
                    </Link>
                </p>
                <GoralysLogoLoader />
            </div>
        </div>
    );
}