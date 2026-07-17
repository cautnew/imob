import { cn } from '@/lib/utils';

export default function SectionHeading({
    eyebrow,
    title,
    description,
    align = 'center',
    className,
}: {
    eyebrow?: string;
    title: string;
    description?: string;
    align?: 'center' | 'left';
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex flex-col gap-3',
                align === 'center'
                    ? 'items-center text-center'
                    : 'items-start text-left',
                className,
            )}
        >
            {eyebrow && (
                <span className="text-sm font-semibold tracking-wide text-primary uppercase">
                    {eyebrow}
                </span>
            )}
            <h2 className="text-3xl font-bold tracking-tight text-balance sm:text-4xl">
                {title}
            </h2>
            {description && (
                <p
                    className={cn(
                        'text-base text-balance text-muted-foreground sm:text-lg',
                        align === 'center' && 'max-w-2xl',
                    )}
                >
                    {description}
                </p>
            )}
        </div>
    );
}
