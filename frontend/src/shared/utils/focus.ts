export type Focus = {
    lng: number;
    lat: number;
    radius: number; // en metros
};

export function focusToParam(focus: Focus | null): string | null {
    if (!focus) return null;

    return `${focus.lng},${focus.lat},${focus.radius}`;
}

export type FocusBbox = {
    minLng: number;
    minLat: number;
    maxLng: number;
    maxLat: number;
};

export function focusBboxToParam(f: FocusBbox | null): string | null {
    if (!f) return null;

    return `${f.minLng},${f.minLat},${f.maxLng},${f.maxLat}`;
}
