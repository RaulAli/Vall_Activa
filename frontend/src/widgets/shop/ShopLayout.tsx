import type { ReactNode } from "react";
import { Header } from "../layout/Header";

type Props = {
    sidebar: ReactNode;
    map: ReactNode;
};

export function ShopLayout({ sidebar, map }: Props) {
    return (
        <div className="flex flex-col h-screen max-h-screen overflow-hidden font-marketplace">
            <Header />
            <main className="flex flex-row flex-1 overflow-hidden min-h-0">
                {/* Left Side: Discovery (60%) */}
                <section className="w-full lg:w-[60%] flex flex-col bg-white dark:bg-background-dark overflow-hidden border-r border-slate-200 dark:border-slate-800 min-h-0">
                    {sidebar}
                </section>

                {/* Right Side: Interactive Map (40%) */}
                <aside className="hidden lg:block lg:w-[40%] bg-slate-100 dark:bg-background-dark relative min-h-0">
                    {map}
                </aside>
            </main>
        </div>
    );
}
