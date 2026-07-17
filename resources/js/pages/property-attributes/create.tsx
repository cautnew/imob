import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import PropertyAttributeForm from '@/components/property-attributes/attribute-form';
import { Card, CardContent } from '@/components/ui/card';
import { index, store } from '@/routes/property-attributes';
import type { BreadcrumbItem } from '@/types';

type TypeOption = {
    value: string;
    label: string;
};

type Props = {
    types: TypeOption[];
};

export default function PropertyAttributesCreate({ types }: Props) {
    return (
        <>
            <Head title="Novo atributo" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo atributo"
                    description="Adicione um atributo dinâmico ao catálogo da sua imobiliária"
                />

                <Card>
                    <CardContent>
                        <PropertyAttributeForm
                            action={store()}
                            types={types}
                            submitLabel="Criar atributo"
                            backHref={index().url}
                        />
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atributos',
        href: index(),
    },
    {
        title: 'Novo atributo',
        href: '',
    },
];

PropertyAttributesCreate.layout = {
    breadcrumbs,
};
