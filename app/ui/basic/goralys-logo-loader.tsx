"use client";

import { ReactElement, useEffect, useRef } from "react";
import { LOGO_PATH } from "@/app/lib/asset";

export default function GoralysLogoLoader(): ReactElement {
    const mainPathRef = useRef<SVGPathElement>(null);
    const eyePathRef = useRef<SVGPathElement>(null);
    const mainColorRef = useRef<SVGPathElement>(null);
    const eyeColorRef = useRef<SVGPathElement>(null);

    useEffect(() => {
        [mainPathRef, eyePathRef, mainColorRef, eyeColorRef].forEach((ref) => {
            const path = ref.current;
            if (!path) return;
            const len = path.getTotalLength();
            path.style.strokeDasharray = String(len);
            path.style.setProperty("--path-length", String(len));
            path.style.strokeDashoffset = String(len);
        });
    }, []);

    return (
        <div className="flex items-center justify-center rounded-xl bg-sky-500 p-4 w-fit mx-auto">
            <style>{`
      .chameleon-path {
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .chameleon-main {
        stroke: #ffffff;
        stroke-width: 4;
        animation: chameleonDraw 9s cubic-bezier(0.37, 0, 0.63, 1) infinite;
      }

      .chameleon-eye {
        stroke: #ffffff;
        stroke-width: 4;
        animation: chameleonDraw 9s cubic-bezier(0.37, 0, 0.63, 1) infinite;
        animation-delay: 0.3s;
      }

      @keyframes chameleonDraw {
        0%   { stroke-dashoffset: var(--path-length); opacity: 0; }
        3%   { opacity: 1; }
        50%  { stroke-dashoffset: 0; opacity: 1; }
        62%  { stroke-dashoffset: 0; opacity: 1; }
        80%  { opacity: 0.6; }
        97%  { opacity: 0; }
        100% { stroke-dashoffset: var(--path-length); opacity: 0; }
      }
    `}</style>

            <svg viewBox="345 350 305 315" fill="none" aria-label="Chargement…" role="img" className="block w-56 h-56">
                <path ref={mainPathRef} className="chameleon-path chameleon-main" d={LOGO_PATH.main} />
                <path ref={eyePathRef} className="chameleon-path chameleon-eye" d={LOGO_PATH.eye} />
            </svg>
        </div>
    );
}
