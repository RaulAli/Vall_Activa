export type Bbox = { minLng: number; minLat: number; maxLng: number; maxLat: number };

export function bboxToParam(b: Bbox | null): string | null {
    if (!b) return null;
    // backend: minLng,minLat,maxLng,maxLat
    return `${b.minLng},${b.minLat},${b.maxLng},${b.maxLat}`;
}
