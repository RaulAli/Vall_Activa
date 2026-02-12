import { useDebounce } from "../../../shared/hooks/useDebounce";
import { useShopStore } from "../../../store/shopStore";
import { useRoutesListQuery } from "../../../features/routes/queries/useRoutesListQuery";
import { RoutesPolylinesLayer } from "../../../features/routes/ui/RoutesPolylinesLayer";

export function RoutesMapPanel() {
    const bbox = useShopStore((s) => s.bbox);

    const routes = useShopStore((s) => s.routes);
    const qDebounced = useDebounce(routes.q, 250);

    const listQuery = useRoutesListQuery({
        bbox,
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

    if (!listQuery.data) return null;

    return <RoutesPolylinesLayer items={listQuery.data.items} />;
}
