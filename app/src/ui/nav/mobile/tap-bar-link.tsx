import { ComponentType, ReactElement, SVGProps } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { clsx } from "clsx";

type Props = {
    name: string;
    url: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    exact?: boolean;
};

export default function TapBarLink({ name, url, icon: Icon, exact = false }: Props): ReactElement {
    const current = usePathname();
    const isActive = exact || url === "/" ? current === url : current === url || current.startsWith(`${url}/`);

    return (
        <Link className={"flex flex-col w-15 items-center"} href={url}>
            <Icon className="w-6 h-6 relative top-1" />
            <p className="text-sm">{name}</p>
            <span
                className={clsx("relative bottom-0 w-full h-0.5", {
                    "bg-sky-400": isActive,
                    "bg-sky-200": !isActive,
                })}
            />
        </Link>
    );
}
