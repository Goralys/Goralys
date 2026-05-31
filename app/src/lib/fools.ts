export function isFoolsDay(): boolean {
    const today = new Date();
    const formatted = today.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
    });
    return formatted === "01/04";
}
