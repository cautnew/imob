import { Link, router, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import AppearanceToggleTab from '@/components/appearance-tabs';
import { PortalNotificationBell } from '@/components/notifications/portal-notification-bell';
import { Button } from '@/components/ui/button';
import { dashboard, logout } from '@/routes/portal';
import { index as billsIndex } from '@/routes/portal/bills';
import { index as leasesIndex } from '@/routes/portal/leases';
import { index as paymentsIndex } from '@/routes/portal/payments';

const navLinks = [
    { href: dashboard(), label: 'Dashboard' },
    { href: leasesIndex(), label: 'Contratos' },
    { href: billsIndex(), label: 'Boletos' },
    { href: paymentsIndex(), label: 'Pagamentos' },
];

export default function PortalHeaderLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-svh flex-col bg-background">
            <header className="border-b border-sidebar-border/50">
                <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-6">
                    <Link href={dashboard()} className="flex items-center">
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
                        <PortalNotificationBell />
                        {auth.user && (
                            <span className="hidden text-sm text-muted-foreground sm:inline">
                                {auth.user.name}
                            </span>
                        )}
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.post(logout().url)}
                        >
                            Sair
                        </Button>
                    </div>
                </div>

                <nav className="flex items-center gap-4 overflow-x-auto border-t border-sidebar-border/50 px-6 py-2 text-sm font-medium text-muted-foreground md:hidden">
                    {navLinks.map((link) => (
                        <Link
                            key={link.label}
                            href={link.href}
                            className="shrink-0 transition-colors hover:text-foreground"
                        >
                            {link.label}
                        </Link>
                    ))}
                </nav>
            </header>

            <main className="flex flex-1 flex-col">{children}</main>
        </div>
    );
}
