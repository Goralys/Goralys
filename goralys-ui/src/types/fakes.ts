import { ComponentType, ReactNode } from "react";

export type GoralysLink = ComponentType<{
    href: string;
    className?: string;
    children: ReactNode;
}>;

export type GoralysImage = ComponentType<{
    src: string;
    alt: string;
    width?: number;
    height?: number;
    className?: string;
    loading?: "lazy" | "eager";
    priority?: boolean;
}>;

export type GoralysRouter = {
    push: (href: string) => void;
    replace: (href: string) => void;
    back: () => void;
    refresh: () => void;
};

export type GoralysSearchParams = {
    get: (name: string) => string | null;
    has: (name: string) => boolean;
    toString: () => string;
};

export type GoralysUIConfig = {
    Link: GoralysLink;
    Image: GoralysImage;
    useRouter: () => GoralysRouter;
    useSearchParams: () => GoralysSearchParams;
    usePathname: () => string;
};
