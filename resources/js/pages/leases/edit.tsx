import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import LeaseForm from '@/components/leases/lease-form';
import { index, show, update } from '@/routes/leases';
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

type EditableLease = {
    id: number;
    property_id: number;
    owner_id: number;
    lessee_id: number;
    start_date: string;
    end_date: string;
    rent_amount: string;
    adjustment_index: string;
    adjustment_interval_months: number;
    renewal_type: string;
    notes: string | null;
};

type Props = {
    lease: EditableLease;
    properties: PropertyOption[];
    owners: NamedOption[];
    lessees: NamedOption[];
    adjustmentIndexes: Option[];
    renewalTypes: Option[];
};

export default function LeasesEdit({
    lease,
    properties,
    owners,
    lessees,
    adjustmentIndexes,
    renewalTypes,
}: Props) {
    return (
        <>
            <Head title={`Editar locação #${lease.id}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar locação"
                    description={`Atualize os dados do contrato #${lease.id}`}
                />

                <LeaseForm
                    action={update(lease.id)}
                    properties={properties}
                    owners={owners}
                    lessees={lessees}
                    adjustmentIndexes={adjustmentIndexes}
                    renewalTypes={renewalTypes}
                    defaultValues={{
                        property_id: String(lease.property_id),
                        owner_id: String(lease.owner_id),
                        lessee_id: String(lease.lessee_id),
                        start_date: lease.start_date,
                        end_date: lease.end_date,
                        rent_amount: lease.rent_amount,
                        adjustment_index: lease.adjustment_index,
                        adjustment_interval_months: String(
                            lease.adjustment_interval_months,
                        ),
                        renewal_type: lease.renewal_type,
                        notes: lease.notes ?? '',
                    }}
                    submitLabel="Salvar alterações"
                    backHref={show(lease.id).url}
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
        title: 'Editar locação',
        href: '',
    },
];

LeasesEdit.layout = {
    breadcrumbs,
};
