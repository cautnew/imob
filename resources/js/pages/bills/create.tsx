import { Head } from '@inertiajs/react';
import BillForm from '@/components/bills/bill-form';
import Heading from '@/components/heading';
import { index, store } from '@/routes/bills';
import type { BreadcrumbItem } from '@/types';

type LeaseOption = {
    id: number;
    property: { id: number; title: string } | null;
};

type Props = {
    leases: LeaseOption[];
};

export default function BillsCreate({ leases }: Props) {
    return (
        <>
            <Head title="Novo boleto" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo boleto"
                    description="Registre um novo boleto vinculado a uma locação"
                />

                <BillForm
                    action={store()}
                    leases={leases}
                    submitLabel="Criar boleto"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boletos',
        href: index(),
    },
    {
        title: 'Novo boleto',
        href: '',
    },
];

BillsCreate.layout = {
    breadcrumbs,
};
