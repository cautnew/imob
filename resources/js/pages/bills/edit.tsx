import { Head } from '@inertiajs/react';
import BillForm from '@/components/bills/bill-form';
import type { BillFormValues } from '@/components/bills/bill-form';
import Heading from '@/components/heading';
import { index, update } from '@/routes/bills';
import type { BreadcrumbItem } from '@/types';

type LeaseOption = {
    id: number;
    property: { id: number; title: string } | null;
};

type EditableBill = {
    id: number;
    lease_id: number;
    due_date: string;
    description: string | null;
};

type Props = {
    bill: EditableBill;
    leases: LeaseOption[];
};

export default function BillsEdit({ bill, leases }: Props) {
    const defaultValues: Partial<BillFormValues> = {
        lease_id: String(bill.lease_id),
        due_date: bill.due_date,
        description: bill.description ?? '',
    };

    return (
        <>
            <Head title="Editar boleto" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar boleto"
                    description="Atualize os dados do boleto"
                />

                <BillForm
                    action={update(bill.id)}
                    leases={leases}
                    defaultValues={defaultValues}
                    submitLabel="Salvar alterações"
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
        title: 'Editar boleto',
        href: '',
    },
];

BillsEdit.layout = {
    breadcrumbs,
};
