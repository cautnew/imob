import { Head } from '@inertiajs/react';
import TransactionForm from '@/components/finance/transaction-form';
import Heading from '@/components/heading';
import { index, store } from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';

type PropertyOption = {
    id: number;
    title: string;
};

type LeaseOption = {
    id: number;
    property: { id: number; title: string } | null;
};

type CategoryOption = {
    id: number;
    name: string;
    type: string;
};

type Props = {
    properties: PropertyOption[];
    leases: LeaseOption[];
    transactionCategories: CategoryOption[];
};

export default function TransactionsCreate({
    properties,
    leases,
    transactionCategories,
}: Props) {
    return (
        <>
            <Head title="Novo lançamento" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo lançamento"
                    description="Registre uma receita ou despesa financeira"
                />

                <TransactionForm
                    action={store()}
                    properties={properties}
                    leases={leases}
                    transactionCategories={transactionCategories}
                    submitLabel="Criar lançamento"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Financeiro',
        href: index(),
    },
    {
        title: 'Novo lançamento',
        href: '',
    },
];

TransactionsCreate.layout = {
    breadcrumbs,
};
