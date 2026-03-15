export const env = {
    // Empty string = relative URLs → requests go through Vite proxy (same-origin, cookies work).
    // In production set VITE_API_BASE_URL to the real backend domain.
    API_BASE_URL: import.meta.env.VITE_API_BASE_URL ?? "",
};
