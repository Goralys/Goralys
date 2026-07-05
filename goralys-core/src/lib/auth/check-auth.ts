export type CheckAuthImpl = () => boolean;

let checkAuthImpl: CheckAuthImpl;

export function configureCheckAuth(impl: CheckAuthImpl): void {
    checkAuthImpl = impl;
}

export function isAuthenticated(): boolean {
    if (!checkAuthImpl) {
        throw new Error("Auth check not configured. Call configureAuthCheck() first.");
    }

    return checkAuthImpl();
}
