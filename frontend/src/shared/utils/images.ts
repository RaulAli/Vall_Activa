export const getFallbackImage = (id: string, type: "route" | "offer") => {
    // Usamos picsum con un seed basado en el id para que siempre sea la misma imagen para el mismo item
    // Pero parezca aleatoria entre diferentes items.
    // loremflickr es bueno porque permite categorización
    const category = type === "route" ? "nature,mountain" : "product,gadget";
    // Usamos el primer segmento del UUID como semilla para el lock
    const seed = id.split('-')[0];
    return `https://loremflickr.com/800/800/${category}?lock=${seed}`;
};
