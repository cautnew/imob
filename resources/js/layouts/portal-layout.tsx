import PortalLayoutTemplate from '@/layouts/portal/portal-header-layout';

export default function PortalLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return <PortalLayoutTemplate>{children}</PortalLayoutTemplate>;
}
