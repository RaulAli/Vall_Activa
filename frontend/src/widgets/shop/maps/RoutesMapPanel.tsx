import { useDebounce } from "../../../shared/hooks/useDebounce";
import { useShopStore } from "../../../store/shopStore";
import { useRoutesMapMarkersQuery } from "../../../features/routes/queries/useRoutesMapMarkersQuery";
import { RoutesMarkersLayer } from "../../../features/routes/ui/RoutesMarkersLayer";

export function RoutesMapPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const routes = useShopStore((s) => s.routes);

    const qDebounced = useDebounce(routes.q, 250);

    const markersQuery = useRoutesMapMarkersQuery({
        bbox,
        focus: routes.focus,

        q: qDebounced,
        sportCode: routes.sportCode,
        distanceMin: routes.distanceMin,
        distanceMax: routes.distanceMax,
        gainMin: routes.gainMin,
        gainMax: routes.gainMax,
    });

    if (!markersQuery.data) return null;

    return <RoutesMarkersLayer items={markersQuery.data} />;
}
