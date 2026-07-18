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

type LeaseOption = {
    id: number;
    property: { id: number; title: string } | null;
};

export type BillFormValues = {
    lease_id: string;
    due_date: string;
    description: string;
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    leases: LeaseOption[];
    defaultValues?: Partial<BillFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function BillForm({
    action,
    leases,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [leaseId, setLeaseId] = useState(defaultValues?.lease_id ?? '');

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
                            <CardTitle>Boleto</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="lease_id">Locação</Label>
                                <Select
                                    name="lease_id"
                                    value={leaseId}
                                    onValueChange={setLeaseId}
                                >
                                    <SelectTrigger
                                        id="lease_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
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
                                <Label htmlFor="description">
                                    Descrição (opcional)
                                </Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    defaultValue={defaultValues?.description}
                                />
                                <InputError message={errors.description} />
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
