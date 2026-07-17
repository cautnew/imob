import { Head, Link } from '@inertiajs/react';
import { Images } from 'lucide-react';
import Heading from '@/components/heading';
import PropertyForm from '@/components/properties/property-form';
import { Button } from '@/components/ui/button';
import { index, update } from '@/routes/properties';
import { index as mediaIndex } from '@/routes/property-media';
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

type PropertyPriceValue = {
    price_type_id: number;
    amount: string;
    frequency: string;
};

type EditableProperty = {
    id: number;
    title: string;
    description: string | null;
    purpose: string;
    type: string;
    status: string;
    zip_code: string;
    street: string;
    number: string | null;
    complement: string | null;
    neighborhood: string;
    city: string;
    state: string;
    latitude: string | null;
    longitude: string | null;
    total_area: string;
    built_area: string | null;
    feature_ids: number[];
    attribute_values: Record<number, string | number | (string | number)[]>;
    prices: PropertyPriceValue[];
};

type Props = {
    property: EditableProperty;
    purposes: Option[];
    types: Option[];
    statuses: Option[];
    featureCategories: FeatureCategory[];
    propertyAttributes: PropertyAttribute[];
    priceTypes: PriceType[];
    frequencies: Option[];
};

export default function PropertiesEdit({
    property,
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
            <Head title={`Editar ${property.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Editar imóvel"
                        description={`Atualize os dados de ${property.title}`}
                    />
                    <Button variant="outline" asChild>
                        <Link href={mediaIndex(property.id)}>
                            <Images />
                            Gerenciar mídias
                        </Link>
                    </Button>
                </div>

                <PropertyForm
                    action={update(property.id)}
                    purposes={purposes}
                    types={types}
                    statuses={statuses}
                    featureCategories={featureCategories}
                    propertyAttributes={propertyAttributes}
                    priceTypes={priceTypes}
                    frequencies={frequencies}
                    defaultValues={{
                        title: property.title,
                        description: property.description ?? '',
                        purpose: property.purpose,
                        type: property.type,
                        status: property.status,
                        zip_code: property.zip_code,
                        street: property.street,
                        number: property.number ?? '',
                        complement: property.complement ?? '',
                        neighborhood: property.neighborhood,
                        city: property.city,
                        state: property.state,
                        latitude: property.latitude ?? '',
                        longitude: property.longitude ?? '',
                        total_area: property.total_area,
                        built_area: property.built_area ?? '',
                        feature_ids: property.feature_ids,
                        attribute_values: property.attribute_values,
                        prices: property.prices,
                    }}
                    submitLabel="Salvar alterações"
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
        title: 'Editar imóvel',
        href: '',
    },
];

PropertiesEdit.layout = {
    breadcrumbs,
};
