import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import AppearanceToggleTab from '@/components/appearance-tabs';
import { Button } from '@/components/ui/button';
import { about, contact, dashboard, home, login, register } from '@/routes';

const navLinks = [
    { href: home(), label: 'Início' },
    { href: about(), label: 'Sobre' },
    { href: contact(), label: 'Contato' },
];

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

                    <nav className="hidden items-center gap-6 text-sm font-medium text-muted-foreground md:flex">
                        {navLinks.map((link) => (
                            <Link
                                key={link.label}
                                href={link.href}
                                className="transition-colors hover:text-foreground"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </nav>

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
                <div className="mx-auto flex w-full max-w-6xl flex-col items-center gap-4 px-6 py-6 text-sm text-muted-foreground sm:flex-row sm:justify-between">
                    <span>
                        &copy; {new Date().getFullYear()}{' '}
                        {import.meta.env.VITE_APP_NAME ?? 'Laravel'}
                    </span>

                    <nav className="flex items-center gap-6">
                        {navLinks.map((link) => (
                            <Link
                                key={link.label}
                                href={link.href}
                                className="transition-colors hover:text-foreground"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </nav>
                </div>
            </footer>
        </div>
    );
}
