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

export type TransactionFormValues = {
    property_id: string;
    lease_id: string;
    transaction_category_id: string;
    description: string;
    amount: string;
    due_date: string;
    notes: string;
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    properties: PropertyOption[];
    leases: LeaseOption[];
    transactionCategories: CategoryOption[];
    defaultValues?: Partial<TransactionFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function TransactionForm({
    action,
    properties,
    leases,
    transactionCategories,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [propertyId, setPropertyId] = useState(
        defaultValues?.property_id ?? '',
    );
    const [leaseId, setLeaseId] = useState(defaultValues?.lease_id ?? '');
    const [categoryId, setCategoryId] = useState(
        defaultValues?.transaction_category_id ?? '',
    );

    const NONE = 'none';

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
                            <CardTitle>Vínculo</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
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
                                                {property.title}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.property_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="lease_id">
                                    Locação (opcional)
                                </Label>
                                <input
                                    type="hidden"
                                    name="lease_id"
                                    value={leaseId}
                                />
                                <Select
                                    value={leaseId || NONE}
                                    onValueChange={(value) =>
                                        setLeaseId(value === NONE ? '' : value)
                                    }
                                >
                                    <SelectTrigger
                                        id="lease_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Nenhuma" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={NONE}>
                                            Nenhuma
                                        </SelectItem>
                                        {leases.map((lease) => (
                                            <SelectItem
                                                key={lease.id}
                                                value={String(lease.id)}
                                            >
                                                Contrato #{lease.id}
                                                {lease.property &&
                                                    ` — ${lease.property.title}`}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.lease_id} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Lançamento</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="description">Descrição</Label>
                                <Input
                                    id="description"
                                    type="text"
                                    required
                                    name="description"
                                    defaultValue={defaultValues?.description}
                                    placeholder="Ex: Aluguel de julho/2026"
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="transaction_category_id">
                                    Categoria
                                </Label>
                                <Select
                                    name="transaction_category_id"
                                    value={categoryId}
                                    onValueChange={setCategoryId}
                                >
                                    <SelectTrigger
                                        id="transaction_category_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {transactionCategories.map(
                                            (category) => (
                                                <SelectItem
                                                    key={category.id}
                                                    value={String(category.id)}
                                                >
                                                    {category.name} (
                                                    {category.type === 'receita'
                                                        ? 'Receita'
                                                        : 'Despesa'}
                                                    )
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={errors.transaction_category_id}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="amount">Valor</Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    name="amount"
                                    defaultValue={defaultValues?.amount}
                                    placeholder="0,00"
                                />
                                <InputError message={errors.amount} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="due_date">Vencimento</Label>
                                <Input
                                    id="due_date"
                                    type="date"
                                    required
                                    name="due_date"
                                    defaultValue={defaultValues?.due_date}
                                />
                                <InputError message={errors.due_date} />
                            </div>

                            <div className="grid gap-2 sm:col-span-2">
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
