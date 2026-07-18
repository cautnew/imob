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

export type LesseeFormValues = {
    name: string;
    birth_date: string;
    marital_status: string;
    occupation: string;
    document: string;
    rg: string;
    rg_issuer: string;
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
    monthly_income: string;
    property_ids: number[];
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    properties: PropertyOption[];
    maritalStatuses: Option[];
    defaultValues?: Partial<LesseeFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function LesseeForm({
    action,
    properties,
    maritalStatuses,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [maritalStatus, setMaritalStatus] = useState(
        defaultValues?.marital_status ?? '',
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
                            <CardTitle>Dados pessoais</CardTitle>
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
                                        placeholder="Nome completo"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="birth_date">
                                        Data de nascimento
                                    </Label>
                                    <Input
                                        id="birth_date"
                                        type="date"
                                        name="birth_date"
                                        defaultValue={
                                            defaultValues?.birth_date
                                        }
                                    />
                                    <InputError message={errors.birth_date} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="marital_status">
                                        Estado civil
                                    </Label>
                                    <Select
                                        name="marital_status"
                                        value={maritalStatus}
                                        onValueChange={setMaritalStatus}
                                    >
                                        <SelectTrigger
                                            id="marital_status"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Selecione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {maritalStatuses.map((option) => (
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
                                        message={errors.marital_status}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="occupation">
                                        Profissão
                                    </Label>
                                    <Input
                                        id="occupation"
                                        type="text"
                                        name="occupation"
                                        defaultValue={
                                            defaultValues?.occupation
                                        }
                                    />
                                    <InputError message={errors.occupation} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Documentos</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-3">
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

                            <div className="grid gap-2">
                                <Label htmlFor="rg">RG</Label>
                                <Input
                                    id="rg"
                                    type="text"
                                    name="rg"
                                    defaultValue={defaultValues?.rg}
                                />
                                <InputError message={errors.rg} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="rg_issuer">
                                    Órgão emissor
                                </Label>
                                <Input
                                    id="rg_issuer"
                                    type="text"
                                    name="rg_issuer"
                                    defaultValue={defaultValues?.rg_issuer}
                                    placeholder="SSP"
                                />
                                <InputError message={errors.rg_issuer} />
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
                            <CardTitle>Renda</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-3">
                            <div className="grid gap-2">
                                <Label htmlFor="monthly_income">
                                    Renda mensal
                                </Label>
                                <Input
                                    id="monthly_income"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="monthly_income"
                                    defaultValue={
                                        defaultValues?.monthly_income
                                    }
                                    placeholder="0,00"
                                />
                                <InputError message={errors.monthly_income} />
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
