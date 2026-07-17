import PublicLayoutTemplate from '@/layouts/public/public-header-layout';

export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return <PublicLayoutTemplate>{children}</PublicLayoutTemplate>;
}
