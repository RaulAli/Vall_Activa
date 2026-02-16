export type Theme = "light" | "dark";

export const getTheme = (): Theme => {
    const saved = localStorage.getItem("theme") as Theme | null;
    if (saved) return saved;
    // Por defecto oscuro como pidió el usuario
    return "dark";
};

export const applyTheme = (theme: Theme) => {
    if (theme === "dark") {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }
    localStorage.setItem("theme", theme);
};

export const toggleTheme = () => {
    const current = getTheme();
    const next = current === "dark" ? "light" : "dark";
    applyTheme(next);
    return next;
};

export const initTheme = () => {
    applyTheme(getTheme());
};
