import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import AppearanceToggleTab from '@/components/appearance-tabs';
import { Button } from '@/components/ui/button';
import { dashboard, home, login, register } from '@/routes';

export default function PublicHeaderLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-svh flex-col bg-background">
            <header className="border-b border-sidebar-border/50">
                <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-6">
                    <Link href={home()} className="flex items-center">
                        <AppLogo />
                    </Link>

                    <div className="flex items-center gap-3">
                        <AppearanceToggleTab className="hidden sm:inline-flex" />

                        {auth.user ? (
                            <Button asChild size="sm">
                                <Link href={dashboard()}>Dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={login()}>Log in</Link>
                                </Button>
                                <Button asChild size="sm">
                                    <Link href={register()}>Register</Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex flex-1 flex-col">{children}</main>

            <footer className="border-t border-sidebar-border/50">
                <div className="mx-auto w-full max-w-6xl px-6 py-6 text-sm text-muted-foreground">
                    &copy; {new Date().getFullYear()}{' '}
                    {import.meta.env.VITE_APP_NAME ?? 'Laravel'}
                </div>
            </footer>
        </div>
    );
}
