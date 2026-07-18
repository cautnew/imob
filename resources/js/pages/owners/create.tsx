import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import OwnerForm from '@/components/owners/owner-form';
import { index, store } from '@/routes/owners';
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
    bankAccountTypes: Option[];
};

export default function OwnersCreate({ properties, bankAccountTypes }: Props) {
    return (
        <>
            <Head title="Novo proprietário" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo proprietário"
                    description="Cadastre um proprietário na sua imobiliária"
                />

                <OwnerForm
                    action={store()}
                    properties={properties}
                    bankAccountTypes={bankAccountTypes}
                    submitLabel="Criar proprietário"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Proprietários',
        href: index(),
    },
    {
        title: 'Novo proprietário',
        href: '',
    },
];

OwnersCreate.layout = {
    breadcrumbs,
};
