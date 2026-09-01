import { ComponentType, ReactElement, SVGProps } from "react";
import { buildArray } from "@goralys/core";
import { AcademicCapIcon, HomeIcon } from "@heroicons/react/24/outline";
import TapBarLink from "@/app/src/ui/nav/mobile/tap-bar-link";
import TapBarUser from "@/app/src/ui/nav/mobile/tap-bar-user";

type Link = {
    name: string;
    url: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
};

export default function TapBar(): ReactElement {
    const links: Link[] = buildArray(
        { name: "Accueil", url: "/", icon: HomeIcon },
        { name: "Sujets", url: "/subject", icon: AcademicCapIcon },
    );

    return (
        <div className="z-50 fixed bottom-0 w-full h-12 bg-white flex flex-row content-center justify-evenly">
            {links.map((l) => (
                <TapBarLink key={l.url} name={l.name} url={l.url} icon={l.icon} />
            ))}
            <TapBarUser />
        </div>
    );
}
