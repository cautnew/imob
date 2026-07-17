import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import PropertyForm from '@/components/properties/property-form';
import { index, store } from '@/routes/properties';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type Feature = {
    id: number;
    name: string;
};

type FeatureCategory = {
    id: number;
    name: string;
    features: Feature[];
};

type PropertyAttributeOption = {
    id: number;
    value: string;
};

type PropertyAttribute = {
    id: number;
    name: string;
    type: string;
    required: boolean;
    options: PropertyAttributeOption[];
};

type PriceType = {
    id: number;
    name: string;
};

type Props = {
    purposes: Option[];
    types: Option[];
    statuses: Option[];
    featureCategories: FeatureCategory[];
    propertyAttributes: PropertyAttribute[];
    priceTypes: PriceType[];
    frequencies: Option[];
};

export default function PropertiesCreate({
    purposes,
    types,
    statuses,
    featureCategories,
    propertyAttributes,
    priceTypes,
    frequencies,
}: Props) {
    return (
        <>
            <Head title="Novo imóvel" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo imóvel"
                    description="Cadastre um imóvel na sua imobiliária"
                />

                <PropertyForm
                    action={store()}
                    purposes={purposes}
                    types={types}
                    statuses={statuses}
                    featureCategories={featureCategories}
                    propertyAttributes={propertyAttributes}
                    priceTypes={priceTypes}
                    frequencies={frequencies}
                    submitLabel="Criar imóvel"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Imóveis',
        href: index(),
    },
    {
        title: 'Novo imóvel',
        href: '',
    },
];

PropertiesCreate.layout = {
    breadcrumbs,
};
