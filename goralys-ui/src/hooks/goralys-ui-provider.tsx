import { GoralysUIConfig } from "#lib/types/fakes";
import { createContext, ReactElement, ReactNode, useContext, useMemo } from "react";

interface Props {
    config: GoralysUIConfig;
    children: ReactNode;
}

const GoralysUIContext = createContext<GoralysUIConfig | null>(null);

export function GoralysUIProvider({ config, children }: Props): ReactElement {
    const value = useMemo(() => config, [config]);

    return <GoralysUIContext.Provider value={value}>{children}</GoralysUIContext.Provider>;
}

export function useGoralysUI(): GoralysUIConfig {
    const context = useContext(GoralysUIContext);
    if (!context) throw new Error("useGoralysUI must be used inside a GoralysUIProvider");

    return context;
}
