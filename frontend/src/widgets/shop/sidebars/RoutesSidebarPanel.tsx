import { useDebounce } from "../../../shared/hooks/useDebounce";
import { Loader } from "../../../shared/ui/Loader";
import { ErrorState } from "../../../shared/ui/ErrorState";
import { useShopStore } from "../../../store/shopStore";

import { useRoutesFiltersQuery } from "../../../features/routes/queries/useRoutesFiltersQuery";
import { useRoutesListQuery } from "../../../features/routes/queries/useRoutesListQuery";
import { RoutesFiltersPanel } from "../../../features/routes/ui/RoutesFiltersPanel";
import { RoutesList } from "../../../features/routes/ui/RoutesList";

export function RoutesSidebarPanel() {
    const bbox = useShopStore((s) => s.bbox);

    const routes = useShopStore((s) => s.routes);
    const setRoutes = useShopStore((s) => s.setRoutes);
    const resetRoutesPage = useShopStore((s) => s.resetRoutesPage);

    const clearRoutesFocusBbox = useShopStore((s) => s.clearRoutesFocusBbox);
    const clearRoutesSelected = useShopStore((s) => s.clearRoutesSelected);

    const qDebounced = useDebounce(routes.q, 250);

    const focusBbox = routes.focusBbox;

    const filtersQuery = useRoutesFiltersQuery({
        bbox,
        focusBbox,
        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
    });

    const listQuery = useRoutesListQuery({
        bbox,
        focusBbox,
        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
        sort: routes.sort,
        order: routes.order,
        page: routes.page,
        limit: routes.limit,
    });

    const hasGeo = !!bbox || !!routes.focusBbox;

    return (
        <div>
            <h2 style={{ margin: "6px 0 12px" }}>Routes</h2>

            {!hasGeo && (
                <div style={{ marginBottom: 12, opacity: 0.8 }}>
                    Mueve el mapa para cargar resultados.
                </div>
            )}

            {/* ✅ Banner de modo foco */}
            {routes.focusBbox && (
                <div
                    style={{
                        border: "1px solid #e5e7eb",
                        borderRadius: 10,
                        padding: 10,
                        marginBottom: 12,
                        background: "#f8fafc",
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                        gap: 10,
                    }}
                >
                    <div style={{ fontSize: 13 }}>
                        <div style={{ fontWeight: 700 }}>Modo foco</div>
                        <div style={{ opacity: 0.8 }}>
                            Area restringida activa
                        </div>
                    </div>

                    <button
                        onClick={() => {
                            clearRoutesSelected();
                            clearRoutesFocusBbox(); // 👈 esto vuelve a bbox normal sin mover mapa
                        }}
                        style={{
                            padding: "8px 10px",
                            border: "1px solid #ddd",
                            borderRadius: 8,
                            background: "white",
                            cursor: "pointer",
                            height: 36,
                        }}
                    >
                        Salir del foco
                    </button>
                </div>
            )}

            {filtersQuery.isLoading && <Loader label="Cargando filtros..." />}
            {filtersQuery.error && <ErrorState message={(filtersQuery.error as Error).message} />}

            {filtersQuery.data && (
                <RoutesFiltersPanel
                    meta={filtersQuery.data}
                    q={routes.q}
                    sportCode={routes.sportCode}
                    onChangeQ={(v) => {
                        setRoutes({ q: v });
                        resetRoutesPage();
                    }}
                    onChangeSportCode={(v) => {
                        setRoutes({ sportCode: v });
                        resetRoutesPage();
                    }}
                />
            )}

            {listQuery.isLoading && <Loader label="Cargando rutas..." />}
            {listQuery.error && <ErrorState message={(listQuery.error as Error).message} />}

            {listQuery.data && (
                <>
                    <div style={{ marginBottom: 10, opacity: 0.8 }}>
                        Total: {listQuery.data.total} · Page {listQuery.data.page}
                    </div>
                    <RoutesList items={listQuery.data.items} />
                </>
            )}
        </div>
    );
}
