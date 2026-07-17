import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import PropertyAttributeForm from '@/components/property-attributes/attribute-form';
import { Card, CardContent } from '@/components/ui/card';
import { index, update } from '@/routes/property-attributes';
import type { BreadcrumbItem } from '@/types';

type TypeOption = {
    value: string;
    label: string;
};

type EditablePropertyAttribute = {
    id: number;
    name: string;
    type: string;
    filterable: boolean;
    comparable: boolean;
    required: boolean;
    options: { id: number; value: string }[];
};

type Props = {
    propertyAttribute: EditablePropertyAttribute;
    types: TypeOption[];
};

export default function PropertyAttributesEdit({
    propertyAttribute,
    types,
}: Props) {
    return (
        <>
            <Head title={`Editar ${propertyAttribute.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar atributo"
                    description={`Atualize os dados de ${propertyAttribute.name}`}
                />

                <Card>
                    <CardContent>
                        <PropertyAttributeForm
                            action={update(propertyAttribute.id)}
                            types={types}
                            defaultValues={{
                                name: propertyAttribute.name,
                                type: propertyAttribute.type,
                                filterable: propertyAttribute.filterable,
                                comparable: propertyAttribute.comparable,
                                required: propertyAttribute.required,
                                options: propertyAttribute.options.map(
                                    (option) => option.value,
                                ),
                            }}
                            submitLabel="Salvar alterações"
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
        title: 'Editar atributo',
        href: '',
    },
];

PropertyAttributesEdit.layout = {
    breadcrumbs,
};
