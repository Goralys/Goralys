type NavigationEvent = { type: "teapot"; toastType: string; toastTitle: string; toastMessage: string } | { type: "redirect"; url: string };

type Listener = (event: NavigationEvent) => void;
const listeners = new Set<Listener>();

export function emitNavigationEvent(event: NavigationEvent): void {
    listeners.forEach((l) => l(event));
}

export function onNavigationEvent(listener: Listener): () => void {
    listeners.add(listener);
    return () => listeners.delete(listener);
}
