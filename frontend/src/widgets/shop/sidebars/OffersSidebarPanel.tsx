import { useDebounce } from "../../../shared/hooks/useDebounce";
import { Loader } from "../../../shared/ui/Loader";
import { ErrorState } from "../../../shared/ui/ErrorState";
import { useShopStore } from "../../../store/shopStore";

import { useOffersFiltersQuery } from "../../../features/offers/queries/useOffersFiltersQuery";
import { useOffersListQuery } from "../../../features/offers/queries/useOffersListQuery";
import { OffersFiltersPanel } from "../../../features/offers/ui/OffersFiltersPanel";
import { OffersList } from "../../../features/offers/ui/OffersList";

export function OffersSidebarPanel() {
    const bbox = useShopStore((s) => s.bbox);
    const offers = useShopStore((s) => s.offers);
    const setOffers = useShopStore((s) => s.setOffers);
    const resetOffersPage = useShopStore((s) => s.resetOffersPage);

    const qDebounced = useDebounce(offers.q, 250);

    const filtersQuery = useOffersFiltersQuery({
        bbox,
        q: qDebounced,
        discountType: offers.discountType,
        inStock: offers.inStock,
    });

    const listQuery = useOffersListQuery({
        bbox,
        q: qDebounced,
        discountType: offers.discountType,
        inStock: offers.inStock,
        sort: offers.sort,
        order: offers.order,
        page: offers.page,
        limit: offers.limit,
    });

    return (
        <div>
            <h2 style={{ margin: "6px 0 12px" }}>Offers</h2>

            {!bbox && <div style={{ marginBottom: 12, opacity: 0.8 }}>Mueve el mapa para cargar resultados.</div>}

            {filtersQuery.isLoading && <Loader label="Cargando filtros..." />}
            {filtersQuery.error && <ErrorState message={(filtersQuery.error as Error).message} />}
            {filtersQuery.data && (
                <OffersFiltersPanel
                    meta={filtersQuery.data}
                    q={offers.q}
                    discountType={offers.discountType}
                    inStock={offers.inStock}
                    onChangeQ={(v) => {
                        setOffers({ q: v });
                        resetOffersPage();
                    }}
                    onToggleInStock={() => {
                        setOffers({ inStock: !offers.inStock });
                        resetOffersPage();
                    }}
                    onChangeDiscountType={(v) => {
                        setOffers({ discountType: v });
                        resetOffersPage();
                    }}
                />
            )}

            {listQuery.isLoading && <Loader label="Cargando ofertas..." />}
            {listQuery.error && <ErrorState message={(listQuery.error as Error).message} />}
            {listQuery.data && (
                <>
                    <div style={{ marginBottom: 10, opacity: 0.8 }}>
                        Total: {listQuery.data.total} · Page {listQuery.data.page}
                    </div>
                    <OffersList items={listQuery.data.items} />
                </>
            )}
        </div>
    );
}
