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
import { Textarea } from '@/components/ui/textarea';
import type { RouteDefinition } from '@/wayfinder';

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

export type LeaseFormValues = {
    property_id: string;
    owner_id: string;
    lessee_id: string;
    start_date: string;
    end_date: string;
    rent_amount: string;
    adjustment_index: string;
    adjustment_interval_months: string;
    renewal_type: string;
    notes: string;
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    properties: PropertyOption[];
    owners: NamedOption[];
    lessees: NamedOption[];
    adjustmentIndexes: Option[];
    renewalTypes: Option[];
    defaultValues?: Partial<LeaseFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function LeaseForm({
    action,
    properties,
    owners,
    lessees,
    adjustmentIndexes,
    renewalTypes,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [propertyId, setPropertyId] = useState(
        defaultValues?.property_id ?? '',
    );
    const [ownerId, setOwnerId] = useState(defaultValues?.owner_id ?? '');
    const [lesseeId, setLesseeId] = useState(defaultValues?.lessee_id ?? '');
    const [adjustmentIndex, setAdjustmentIndex] = useState(
        defaultValues?.adjustment_index ?? '',
    );
    const [renewalType, setRenewalType] = useState(
        defaultValues?.renewal_type ?? '',
    );

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
                            <CardTitle>Partes envolvidas</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-3">
                            <div className="grid gap-2">
                                <Label htmlFor="property_id">Imóvel</Label>
                                <Select
                                    name="property_id"
                                    value={propertyId}
                                    onValueChange={setPropertyId}
                                >
                                    <SelectTrigger
                                        id="property_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {properties.map((property) => (
                                            <SelectItem
                                                key={property.id}
                                                value={String(property.id)}
                                            >
                                                {property.title} —{' '}
                                                {property.city}/{property.state}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.property_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="owner_id">Proprietário</Label>
                                <Select
                                    name="owner_id"
                                    value={ownerId}
                                    onValueChange={setOwnerId}
                                >
                                    <SelectTrigger
                                        id="owner_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {owners.map((owner) => (
                                            <SelectItem
                                                key={owner.id}
                                                value={String(owner.id)}
                                            >
                                                {owner.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.owner_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="lessee_id">Inquilino</Label>
                                <Select
                                    name="lessee_id"
                                    value={lesseeId}
                                    onValueChange={setLesseeId}
                                >
                                    <SelectTrigger
                                        id="lessee_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {lessees.map((lessee) => (
                                            <SelectItem
                                                key={lessee.id}
                                                value={String(lessee.id)}
                                            >
                                                {lessee.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.lessee_id} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Vigência</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="start_date">Início</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    required
                                    name="start_date"
                                    defaultValue={defaultValues?.start_date}
                                />
                                <InputError message={errors.start_date} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="end_date">Fim</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    required
                                    name="end_date"
                                    defaultValue={defaultValues?.end_date}
                                />
                                <InputError message={errors.end_date} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Aluguel e reajuste</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-3">
                            <div className="grid gap-2">
                                <Label htmlFor="rent_amount">
                                    Valor do aluguel
                                </Label>
                                <Input
                                    id="rent_amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    name="rent_amount"
                                    defaultValue={defaultValues?.rent_amount}
                                    placeholder="0,00"
                                />
                                <InputError message={errors.rent_amount} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="adjustment_index">
                                    Índice de reajuste
                                </Label>
                                <Select
                                    name="adjustment_index"
                                    value={adjustmentIndex}
                                    onValueChange={setAdjustmentIndex}
                                >
                                    <SelectTrigger
                                        id="adjustment_index"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {adjustmentIndexes.map((option) => (
                                            <SelectItem
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.adjustment_index} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="adjustment_interval_months">
                                    Periodicidade (meses)
                                </Label>
                                <Input
                                    id="adjustment_interval_months"
                                    type="number"
                                    step="1"
                                    min="1"
                                    max="60"
                                    required
                                    name="adjustment_interval_months"
                                    defaultValue={
                                        defaultValues?.adjustment_interval_months ??
                                        '12'
                                    }
                                />
                                <InputError
                                    message={errors.adjustment_interval_months}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Renovação e observações</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-2 sm:w-1/3">
                                <Label htmlFor="renewal_type">
                                    Tipo de renovação
                                </Label>
                                <Select
                                    name="renewal_type"
                                    value={renewalType}
                                    onValueChange={setRenewalType}
                                >
                                    <SelectTrigger
                                        id="renewal_type"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {renewalTypes.map((option) => (
                                            <SelectItem
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.renewal_type} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Observações</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    defaultValue={defaultValues?.notes}
                                />
                                <InputError message={errors.notes} />
                            </div>
                        </CardContent>
                    </Card>

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
