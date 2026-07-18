import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import LeaseForm from '@/components/leases/lease-form';
import { index, store } from '@/routes/leases';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type NamedOption = {
    id: number;
    name: string;
};

type PropertyOption = {
    id: number;
    title: string;
    city: string;
    state: string;
};

type Props = {
    properties: PropertyOption[];
    owners: NamedOption[];
    lessees: NamedOption[];
    adjustmentIndexes: Option[];
    renewalTypes: Option[];
};

export default function LeasesCreate({
    properties,
    owners,
    lessees,
    adjustmentIndexes,
    renewalTypes,
}: Props) {
    return (
        <>
            <Head title="Nova locação" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Nova locação"
                    description="Cadastre um contrato de locação"
                />

                <LeaseForm
                    action={store()}
                    properties={properties}
                    owners={owners}
                    lessees={lessees}
                    adjustmentIndexes={adjustmentIndexes}
                    renewalTypes={renewalTypes}
                    submitLabel="Criar locação"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Locações',
        href: index(),
    },
    {
        title: 'Nova locação',
        href: '',
    },
];

LeasesCreate.layout = {
    breadcrumbs,
};
