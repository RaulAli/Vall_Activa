import { useDebounce } from "../../../shared/hooks/useDebounce";
import { useShopStore } from "../../../store/shopStore";

import { useRoutesMapMarkersQuery } from "../../../features/routes/queries/useRoutesMapMarkersQuery";
import { RoutesMarkersLayer } from "../../../features/routes/ui/RoutesMarkersLayer";

import { useRouteDetailsQuery } from "../../../features/routes/queries/useRouteDetailsQuery";
import { SelectedRoutePolylineLayer } from "../../../features/routes/ui/SelectedRoutePolylineLayer";

export function RoutesMapPanel() {
    const bbox = useShopStore((s) => s.bbox);

    const routes = useShopStore((s) => s.routes);
    const setRoutesSelected = useShopStore((s) => s.setRoutesSelected);

    const qDebounced = useDebounce(routes.q, 250);

    // Markers/clusters
    const markersQuery = useRoutesMapMarkersQuery({
        bbox,
        focusBbox: routes.focusBbox,
        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
        difficulty: routes.difficulty,
        routeType: routes.routeType,
        durationMin: routes.durationMin,
        durationMax: routes.durationMax,
    });

    // Details para polyline seleccionada (solo si hay selected)
    const selectedSlug = routes.selected?.slug ?? null;
    const detailsQuery = useRouteDetailsQuery(selectedSlug);

    // Si llega polyline del details, la guardamos en store para evitar refetch si lo vuelves a abrir
    if (
        routes.selected &&
        routes.selected.polyline === null &&
        detailsQuery.data &&
        typeof detailsQuery.data.polyline === "string"
    ) {
        setRoutesSelected({ slug: routes.selected.slug, polyline: detailsQuery.data.polyline });
    }

    if (!markersQuery.data) return null;

    const selectedPolyline =
        routes.focusBbox && routes.selected?.polyline ? routes.selected.polyline : null;

    const selectedTitle = detailsQuery.data?.title ?? routes.selected?.slug ?? "";

    return (
        <>
            <RoutesMarkersLayer items={markersQuery.data.items} />

            {/* SOLO en modo foco y SOLO si hay seleccion */}
            {routes.focusBbox && selectedPolyline && (
                <SelectedRoutePolylineLayer
                    slug={routes.selected!.slug}
                    title={selectedTitle}
                    encoded={selectedPolyline}
                />
            )}
        </>
    );
}
