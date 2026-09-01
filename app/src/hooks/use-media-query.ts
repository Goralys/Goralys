"use client";
import { useEffect, useState } from "react";

export function useMediaQuery(query: string): boolean {
    const [matches, setMatches] = useState(false);

    useEffect(() => {
        const run = () => {
            const media = window.matchMedia(query);
            setMatches(media.matches);

            const listener = (e: MediaQueryListEvent): void => setMatches(e.matches);
            media.addEventListener("change", listener);

            return (): void => media.removeEventListener("change", listener);
        };

        return run();
    }, [query]);

    return matches;
}
