export function Footer() {
    return (
        <footer className="bg-white dark:bg-gray-900 border-t border-[#e7ebf3] dark:border-slate-800 py-12">
            <div className="max-w-[1240px] mx-auto px-10">
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div className="flex items-center gap-4 text-primary">
                        <div className="size-6">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 4C25.7818 14.2173 33.7827 22.2182 44 24C33.7827 25.7818 25.7818 33.7827 24 44C22.2182 33.7827 14.2173 25.7818 4 24C14.2173 22.2182 22.2182 14.2173 24 4Z" fill="currentColor"></path>
                            </svg>
                        </div>
                        <h2 className="text-[#0d121b] dark:text-white text-lg font-bold">TrailQuest</h2>
                    </div>
                    <div className="flex gap-8 text-sm text-gray-500 dark:text-gray-400">
                        <a className="hover:text-primary transition-colors" href="#">About</a>
                        <a className="hover:text-primary transition-colors" href="#">Safety</a>
                        <a className="hover:text-primary transition-colors" href="#">Terms</a>
                        <a className="hover:text-primary transition-colors" href="#">Privacy</a>
                    </div>
                    <p className="text-sm text-gray-500 dark:text-gray-400">© 2024 TrailQuest. All rights reserved.</p>
                </div>
            </div>
        </footer>
    );
}
