import { Badge } from '@/components/ui/badge';

export default function PageHeader({
    eyebrow,
    title,
    description,
}: {
    eyebrow: string;
    title: string;
    description: string;
}) {
    return (
        <section className="border-b border-sidebar-border/50">
            <div className="mx-auto flex max-w-3xl flex-col items-center gap-4 px-6 py-16 text-center lg:py-24">
                <Badge variant="secondary" className="px-3 py-1">
                    {eyebrow}
                </Badge>
                <h1 className="text-4xl font-bold tracking-tight text-balance sm:text-5xl">
                    {title}
                </h1>
                <p className="text-lg text-balance text-muted-foreground">
                    {description}
                </p>
            </div>
        </section>
    );
}
