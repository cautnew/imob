import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import LesseeForm from '@/components/lessees/lessee-form';
import { index, update } from '@/routes/lessees';
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

type EditableLessee = {
    id: number;
    name: string;
    birth_date: string | null;
    marital_status: string | null;
    occupation: string | null;
    document: string;
    rg: string | null;
    rg_issuer: string | null;
    phone: string;
    mobile: string | null;
    email: string | null;
    zip_code: string;
    street: string;
    number: string | null;
    complement: string | null;
    neighborhood: string;
    city: string;
    state: string;
    monthly_income: string | null;
    property_ids: number[];
};

type Props = {
    lessee: EditableLessee;
    properties: PropertyOption[];
    maritalStatuses: Option[];
};

export default function LesseesEdit({
    lessee,
    properties,
    maritalStatuses,
}: Props) {
    return (
        <>
            <Head title={`Editar ${lessee.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar inquilino"
                    description={`Atualize os dados de ${lessee.name}`}
                />

                <LesseeForm
                    action={update(lessee.id)}
                    properties={properties}
                    maritalStatuses={maritalStatuses}
                    defaultValues={{
                        name: lessee.name,
                        birth_date: lessee.birth_date ?? '',
                        marital_status: lessee.marital_status ?? '',
                        occupation: lessee.occupation ?? '',
                        document: lessee.document,
                        rg: lessee.rg ?? '',
                        rg_issuer: lessee.rg_issuer ?? '',
                        phone: lessee.phone,
                        mobile: lessee.mobile ?? '',
                        email: lessee.email ?? '',
                        zip_code: lessee.zip_code,
                        street: lessee.street,
                        number: lessee.number ?? '',
                        complement: lessee.complement ?? '',
                        neighborhood: lessee.neighborhood,
                        city: lessee.city,
                        state: lessee.state,
                        monthly_income: lessee.monthly_income ?? '',
                        property_ids: lessee.property_ids,
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
        title: 'Inquilinos',
        href: index(),
    },
    {
        title: 'Editar inquilino',
        href: '',
    },
];

LesseesEdit.layout = {
    breadcrumbs,
};
