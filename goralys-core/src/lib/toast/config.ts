import { ToastFn } from "@/types/toast";

export interface ToastConfig {
    getShowToast: () => ToastFn;
}

let config: ToastConfig | null = null;

export function configureToast(cfg: ToastConfig): void {
    config = cfg;
}

export function getToastConfig(): ToastConfig {
    if (!config) throw new Error("Toasts have not been configured yet. Call configureToast() first.");

    return config;
}
