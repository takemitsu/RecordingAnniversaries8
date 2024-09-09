import {Link, Head} from '@inertiajs/react';
import {PageProps} from '@/types';

export default function Welcome({auth, laravelVersion, phpVersion}: PageProps<{
    laravelVersion: string,
    phpVersion: string
}>) {
    return (
        <>
            <Head />
            <div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
                <div
                    className="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                    <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                        <main className="justify-center">
                            <div className="flex items-center justify-center gap-6">
                                <div
                                    className="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    ra
                                </div>

                                <div>
                                    <h2 className="text-xl font-semibold text-black dark:text-white">Recording
                                        Anniversary</h2>

                                    <p className="mt-2 text-sm/relaxed">
                                        Would be cool to add a description of ‘Recording Anniversary’ here someday, but might not end up doing it.
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2 mt-3">
                                <nav className="flex flex-1 justify-center">
                                    {auth.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                        >
                                            Dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            <Link
                                                href={route('login')}
                                                className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                            >
                                                Log in
                                            </Link>
                                            <Link
                                                href={route('register')}
                                                className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                            >
                                                Register
                                            </Link>
                                        </>
                                    )}
                                </nav>
                            </div>
                            <div className="flex items-center gap-2 mt-3">
                                <nav className="flex flex-1 justify-center">
                                    <Link
                                        href={route('years')}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                    >
                                        年度一覧
                                    </Link>
                                </nav>
                            </div>
                        </main>

                        <footer className="pt-8 text-center text-sm text-black dark:text-white/70">
                            Laravel v{laravelVersion} (PHP v{phpVersion})
                        </footer>
                    </div>
                </div>
            </div>
        </>
    );
}
