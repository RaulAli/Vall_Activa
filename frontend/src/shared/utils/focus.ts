export type Focus = {
    lng: number;
    lat: number;
    radius: number; // en metros
};

export function focusToParam(focus: Focus | null): string | null {
    if (!focus) return null;

    return `${focus.lng},${focus.lat},${focus.radius}`;
}
