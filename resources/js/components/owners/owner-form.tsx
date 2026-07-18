import { Form } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { RouteDefinition } from '@/wayfinder';

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

export type OwnerFormValues = {
    name: string;
    document: string;
    phone: string;
    mobile: string;
    email: string;
    zip_code: string;
    street: string;
    number: string;
    complement: string;
    neighborhood: string;
    city: string;
    state: string;
    bank_name: string;
    bank_agency: string;
    bank_account: string;
    bank_account_type: string;
    pix_key: string;
    property_ids: number[];
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    properties: PropertyOption[];
    bankAccountTypes: Option[];
    defaultValues?: Partial<OwnerFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function OwnerForm({
    action,
    properties,
    bankAccountTypes,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [bankAccountType, setBankAccountType] = useState(
        defaultValues?.bank_account_type ?? '',
    );

    const isPropertyChecked = (propertyId: number) =>
        (defaultValues?.property_ids ?? []).includes(propertyId);

    return (
        <Form
            action={action}
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ processing, errors }) => (
                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Dados básicos</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nome</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        name="name"
                                        defaultValue={defaultValues?.name}
                                        placeholder="Nome completo ou razão social"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="document">CPF/CNPJ</Label>
                                    <Input
                                        id="document"
                                        type="text"
                                        required
                                        name="document"
                                        defaultValue={defaultValues?.document}
                                        placeholder="000.000.000-00"
                                    />
                                    <InputError message={errors.document} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Contato</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-3">
                            <div className="grid gap-2">
                                <Label htmlFor="phone">Telefone</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    required
                                    name="phone"
                                    defaultValue={defaultValues?.phone}
                                    placeholder="(00) 0000-0000"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="mobile">Celular/WhatsApp</Label>
                                <Input
                                    id="mobile"
                                    type="text"
                                    name="mobile"
                                    defaultValue={defaultValues?.mobile}
                                    placeholder="(00) 00000-0000"
                                />
                                <InputError message={errors.mobile} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mail</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    defaultValue={defaultValues?.email}
                                />
                                <InputError message={errors.email} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Endereço</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="zip_code">CEP</Label>
                                    <Input
                                        id="zip_code"
                                        type="text"
                                        required
                                        name="zip_code"
                                        defaultValue={defaultValues?.zip_code}
                                        placeholder="00000-000"
                                    />
                                    <InputError message={errors.zip_code} />
                                </div>

                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="street">Logradouro</Label>
                                    <Input
                                        id="street"
                                        type="text"
                                        required
                                        name="street"
                                        defaultValue={defaultValues?.street}
                                    />
                                    <InputError message={errors.street} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="number">Número</Label>
                                    <Input
                                        id="number"
                                        type="text"
                                        name="number"
                                        defaultValue={defaultValues?.number}
                                    />
                                    <InputError message={errors.number} />
                                </div>

                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="complement">
                                        Complemento
                                    </Label>
                                    <Input
                                        id="complement"
                                        type="text"
                                        name="complement"
                                        defaultValue={defaultValues?.complement}
                                    />
                                    <InputError message={errors.complement} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-4">
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="neighborhood">Bairro</Label>
                                    <Input
                                        id="neighborhood"
                                        type="text"
                                        required
                                        name="neighborhood"
                                        defaultValue={
                                            defaultValues?.neighborhood
                                        }
                                    />
                                    <InputError message={errors.neighborhood} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="city">Cidade</Label>
                                    <Input
                                        id="city"
                                        type="text"
                                        required
                                        name="city"
                                        defaultValue={defaultValues?.city}
                                    />
                                    <InputError message={errors.city} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="state">UF</Label>
                                    <Input
                                        id="state"
                                        type="text"
                                        required
                                        maxLength={2}
                                        name="state"
                                        className="uppercase"
                                        defaultValue={defaultValues?.state}
                                    />
                                    <InputError message={errors.state} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Dados bancários</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="bank_name">Banco</Label>
                                    <Input
                                        id="bank_name"
                                        type="text"
                                        name="bank_name"
                                        defaultValue={defaultValues?.bank_name}
                                    />
                                    <InputError message={errors.bank_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="bank_agency">Agência</Label>
                                    <Input
                                        id="bank_agency"
                                        type="text"
                                        name="bank_agency"
                                        defaultValue={
                                            defaultValues?.bank_agency
                                        }
                                    />
                                    <InputError message={errors.bank_agency} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="bank_account">Conta</Label>
                                    <Input
                                        id="bank_account"
                                        type="text"
                                        name="bank_account"
                                        defaultValue={
                                            defaultValues?.bank_account
                                        }
                                    />
                                    <InputError message={errors.bank_account} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="bank_account_type">
                                        Tipo de conta
                                    </Label>
                                    <Select
                                        name="bank_account_type"
                                        value={bankAccountType}
                                        onValueChange={setBankAccountType}
                                    >
                                        <SelectTrigger
                                            id="bank_account_type"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Selecione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {bankAccountTypes.map((option) => (
                                                <SelectItem
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        message={errors.bank_account_type}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="pix_key">Chave PIX</Label>
                                    <Input
                                        id="pix_key"
                                        type="text"
                                        name="pix_key"
                                        defaultValue={defaultValues?.pix_key}
                                    />
                                    <InputError message={errors.pix_key} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {properties.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Imóveis</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-2">
                                <div className="flex flex-col gap-2">
                                    {properties.map((property) => (
                                        <label
                                            key={property.id}
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                name="property_ids[]"
                                                value={property.id}
                                                defaultChecked={isPropertyChecked(
                                                    property.id,
                                                )}
                                                className="size-4 rounded border-input accent-primary"
                                            />
                                            {property.title} — {property.city}/
                                            {property.state}
                                        </label>
                                    ))}
                                </div>
                                <InputError message={errors.property_ids} />
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            {submitLabel}
                        </Button>
                        <Button variant="outline" asChild>
                            <a href={backHref}>Cancelar</a>
                        </Button>
                    </div>
                </div>
            )}
        </Form>
    );
}
