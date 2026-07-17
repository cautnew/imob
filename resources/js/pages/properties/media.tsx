import { Head, Link, setLayoutProps } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import MediaGallery from '@/components/properties/media-gallery';
import type {MediaItem} from '@/components/properties/media-gallery';
import { Button } from '@/components/ui/button';
import { index, edit } from '@/routes/properties';
import type { BreadcrumbItem } from '@/types';

type Props = {
    property: {
        id: number;
        title: string;
    };
    media: MediaItem[];
};

export default function PropertiesMedia({ property, media }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Imóveis', href: index() },
        { title: property.title, href: edit(property.id) },
        { title: 'Mídias', href: '' },
    ];

    setLayoutProps({ breadcrumbs });

    return (
        <>
            <Head title={`Mídias de ${property.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Mídias"
                        description={`Gerencie as fotos de ${property.title}`}
                    />
                    <Button variant="outline" asChild>
                        <Link href={edit(property.id)}>
                            <ArrowLeft />
                            Voltar para o imóvel
                        </Link>
                    </Button>
                </div>

                <MediaGallery propertyId={property.id} media={media} />
            </div>
        </>
    );
}
