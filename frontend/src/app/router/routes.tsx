import { createBrowserRouter } from "react-router-dom";
import { ShopPage } from "../../pages/ShopPage";

export const router = createBrowserRouter([
    { path: "/", element: <ShopPage /> },
]);
