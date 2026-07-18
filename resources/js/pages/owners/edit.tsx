import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import OwnerForm from '@/components/owners/owner-form';
import { index, update } from '@/routes/owners';
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

type EditableOwner = {
    id: number;
    name: string;
    document: string;
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
    bank_name: string | null;
    bank_agency: string | null;
    bank_account: string | null;
    bank_account_type: string | null;
    pix_key: string | null;
    property_ids: number[];
};

type Props = {
    owner: EditableOwner;
    properties: PropertyOption[];
    bankAccountTypes: Option[];
};

export default function OwnersEdit({
    owner,
    properties,
    bankAccountTypes,
}: Props) {
    return (
        <>
            <Head title={`Editar ${owner.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar proprietário"
                    description={`Atualize os dados de ${owner.name}`}
                />

                <OwnerForm
                    action={update(owner.id)}
                    properties={properties}
                    bankAccountTypes={bankAccountTypes}
                    defaultValues={{
                        name: owner.name,
                        document: owner.document,
                        phone: owner.phone,
                        mobile: owner.mobile ?? '',
                        email: owner.email ?? '',
                        zip_code: owner.zip_code,
                        street: owner.street,
                        number: owner.number ?? '',
                        complement: owner.complement ?? '',
                        neighborhood: owner.neighborhood,
                        city: owner.city,
                        state: owner.state,
                        bank_name: owner.bank_name ?? '',
                        bank_agency: owner.bank_agency ?? '',
                        bank_account: owner.bank_account ?? '',
                        bank_account_type: owner.bank_account_type ?? '',
                        pix_key: owner.pix_key ?? '',
                        property_ids: owner.property_ids,
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
        title: 'Proprietários',
        href: index(),
    },
    {
        title: 'Editar proprietário',
        href: '',
    },
];

OwnersEdit.layout = {
    breadcrumbs,
};
