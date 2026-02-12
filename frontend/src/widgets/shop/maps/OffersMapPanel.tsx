import { useDebounce } from "../../../shared/hooks/useDebounce";
import { useShopStore } from "../../../store/shopStore";
import { useOfferMapBusinessesQuery } from "../../../features/offers/queries/useOfferMapBusinessesQuery";
import { BusinessMarkersLayer } from "../../../features/offers/ui/BusinessMarkersLayer";

export function OffersMapPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const offers = useShopStore((s) => s.offers);

    const qDebounced = useDebounce(offers.q, 250);

    const markersQuery = useOfferMapBusinessesQuery({
        bbox,
        q: qDebounced,
        discountType: offers.discountType,
        inStock: offers.inStock,
    });

    if (!markersQuery.data) return null;

    return <BusinessMarkersLayer items={markersQuery.data} />;
}
