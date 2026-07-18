import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import LesseeForm from '@/components/lessees/lessee-form';
import { index, store } from '@/routes/lessees';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type PropertyOption = {
    id: number;
    title: string;
    city: string;
    state: string;
};

type Props = {
    properties: PropertyOption[];
    maritalStatuses: Option[];
};

export default function LesseesCreate({ properties, maritalStatuses }: Props) {
    return (
        <>
            <Head title="Novo inquilino" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo inquilino"
                    description="Cadastre um inquilino na sua imobiliária"
                />

                <LesseeForm
                    action={store()}
                    properties={properties}
                    maritalStatuses={maritalStatuses}
                    submitLabel="Criar inquilino"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inquilinos',
        href: index(),
    },
    {
        title: 'Novo inquilino',
        href: '',
    },
];

LesseesCreate.layout = {
    breadcrumbs,
};
