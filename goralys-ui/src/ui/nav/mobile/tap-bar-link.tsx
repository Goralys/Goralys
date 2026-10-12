import { ComponentType, ReactElement, SVGProps } from "react";
import { clsx } from "clsx";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

type Props = {
    name: string;
    url: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    exact?: boolean;
};

export function TapBarLink({ name, url, icon: Icon, exact = false }: Props): ReactElement {
    const Link = useGoralysUI().Link;

    const current = useGoralysUI().usePathname();
    const isActive = exact || url === "/" ? current === url : current === url || current.startsWith(`${url}/`);

    return (
        <Link className={"flex flex-col w-20 items-center"} href={url}>
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
