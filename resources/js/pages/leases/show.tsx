import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import LeaseDocuments from '@/components/leases/lease-documents';
import type { LeaseDocumentItem } from '@/components/leases/lease-documents';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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
import { usePermissions } from '@/hooks/use-permissions';
import { destroy, edit, index } from '@/routes/leases';
import { store as storeAdjustment } from '@/routes/leases/adjustments';
import { store as storeRenewal } from '@/routes/leases/renewals';
import { update as updateStatus } from '@/routes/leases/status';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type LeaseDetail = {
    id: number;
    start_date: string;
    end_date: string;
    rent_amount: string;
    adjustment_index: string;
    adjustment_interval_months: number;
    last_adjustment_date: string | null;
    renewal_type: string;
    status: string;
    notes: string | null;
    property: { id: number; title: string; city: string; state: string };
    owner: { id: number; name: string };
    lessee: { id: number; name: string };
};

type LeaseEventRow = {
    id: number;
    type: string;
    type_label: string;
    occurred_on: string;
    description: string;
};

type Props = {
    lease: LeaseDetail;
    events: LeaseEventRow[];
    statuses: Option[];
    adjustmentIndexes: Option[];
    renewalTypes: Option[];
    documents: LeaseDocumentItem[];
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

export default function LeasesShow({
    lease,
    events,
    statuses,
    adjustmentIndexes,
    renewalTypes,
    documents,
}: Props) {
    const { can } = usePermissions();

    const canEdit = can('locacoes.editar');
    const canDelete = can('locacoes.excluir');

    const [status, setStatus] = useState(lease.status);

    const statusLabel = (value: string) =>
        statuses.find((option) => option.value === value)?.label ?? value;

    const adjustmentIndexLabel = (value: string) =>
        adjustmentIndexes.find((option) => option.value === value)?.label ??
        value;

    const renewalTypeLabel = (value: string) =>
        renewalTypes.find((option) => option.value === value)?.label ?? value;

    return (
        <>
            <Head title={`Locação — ${lease.property.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={`Locação — ${lease.property.title}`}
                        description={`${lease.owner.name} → ${lease.lessee.name}`}
                    />
                    <div className="flex items-center gap-2">
                        {canEdit && (
                            <Button variant="outline" asChild>
                                <Link href={edit(lease.id)}>
                                    <Pencil />
                                    Editar
                                </Link>
                            </Button>
                        )}
                        {canDelete && (
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        <Trash2 />
                                        Excluir
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>
                                        Excluir locação de{' '}
                                        {lease.property.title}?
                                    </DialogTitle>
                                    <DialogDescription>
                                        Essa ação não pode ser desfeita.
                                    </DialogDescription>
                                    <Form action={destroy(lease.id)}>
                                        {({ processing, errors }) => (
                                            <>
                                                <InputError
                                                    message={errors.lease}
                                                />
                                                <DialogFooter className="gap-2">
                                                    <DialogClose asChild>
                                                        <Button variant="secondary">
                                                            Cancelar
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        variant="destructive"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        Excluir
                                                    </Button>
                                                </DialogFooter>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Detalhes do contrato</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Situação
                                </p>
                                <Badge variant="outline">
                                    {statusLabel(lease.status)}
                                </Badge>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Imóvel
                                </p>
                                <p>
                                    {lease.property.title} —{' '}
                                    {lease.property.city}/{lease.property.state}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Início
                                </p>
                                <p>{formatDate(lease.start_date)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Fim
                                </p>
                                <p>{formatDate(lease.end_date)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Valor do aluguel
                                </p>
                                <p>{formatCurrency(lease.rent_amount)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Reajuste
                                </p>
                                <p>
                                    {adjustmentIndexLabel(
                                        lease.adjustment_index,
                                    )}{' '}
                                    a cada {lease.adjustment_interval_months}{' '}
                                    meses
                                    {lease.last_adjustment_date &&
                                        ` (último em ${formatDate(lease.last_adjustment_date)})`}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Renovação
                                </p>
                                <p>{renewalTypeLabel(lease.renewal_type)}</p>
                            </div>
                            {lease.notes && (
                                <div className="sm:col-span-2">
                                    <p className="text-sm text-muted-foreground">
                                        Observações
                                    </p>
                                    <p>{lease.notes}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {canEdit && (
                        <div className="flex flex-col gap-4">
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        Aplicar reajuste
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Aplicar reajuste</DialogTitle>
                                    <DialogDescription>
                                        Registre o novo valor do aluguel e a
                                        data efetiva do reajuste.
                                    </DialogDescription>
                                    <Form action={storeAdjustment(lease.id)}>
                                        {({ processing, errors }) => (
                                            <div className="grid gap-4">
                                                <div className="grid gap-2">
                                                    <Label htmlFor="rent_amount">
                                                        Novo valor do aluguel
                                                    </Label>
                                                    <Input
                                                        id="rent_amount"
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                        required
                                                        name="rent_amount"
                                                        defaultValue={
                                                            lease.rent_amount
                                                        }
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.rent_amount
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="effective_date">
                                                        Data efetiva
                                                    </Label>
                                                    <Input
                                                        id="effective_date"
                                                        type="date"
                                                        required
                                                        name="effective_date"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.effective_date
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="adjustment_notes">
                                                        Observações
                                                    </Label>
                                                    <Textarea
                                                        id="adjustment_notes"
                                                        name="notes"
                                                    />
                                                    <InputError
                                                        message={errors.notes}
                                                    />
                                                </div>
                                                <DialogFooter className="gap-2">
                                                    <DialogClose asChild>
                                                        <Button variant="secondary">
                                                            Cancelar
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {processing && (
                                                            <Spinner />
                                                        )}
                                                        Confirmar
                                                    </Button>
                                                </DialogFooter>
                                            </div>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>

                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        Renovar contrato
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Renovar contrato</DialogTitle>
                                    <DialogDescription>
                                        Estenda a vigência do contrato para uma
                                        nova data de término.
                                    </DialogDescription>
                                    <Form action={storeRenewal(lease.id)}>
                                        {({ processing, errors }) => (
                                            <div className="grid gap-4">
                                                <div className="grid gap-2">
                                                    <Label htmlFor="end_date">
                                                        Nova data de término
                                                    </Label>
                                                    <Input
                                                        id="end_date"
                                                        type="date"
                                                        required
                                                        name="end_date"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.end_date
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="renewal_notes">
                                                        Observações
                                                    </Label>
                                                    <Textarea
                                                        id="renewal_notes"
                                                        name="notes"
                                                    />
                                                    <InputError
                                                        message={errors.notes}
                                                    />
                                                </div>
                                                <DialogFooter className="gap-2">
                                                    <DialogClose asChild>
                                                        <Button variant="secondary">
                                                            Cancelar
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {processing && (
                                                            <Spinner />
                                                        )}
                                                        Confirmar
                                                    </Button>
                                                </DialogFooter>
                                            </div>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>

                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        Alterar situação
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Alterar situação</DialogTitle>
                                    <DialogDescription>
                                        Atualize a situação do contrato.
                                    </DialogDescription>
                                    <Form action={updateStatus(lease.id)}>
                                        {({ processing, errors }) => (
                                            <div className="grid gap-4">
                                                <div className="grid gap-2">
                                                    <Label htmlFor="status">
                                                        Situação
                                                    </Label>
                                                    <Select
                                                        name="status"
                                                        value={status}
                                                        onValueChange={
                                                            setStatus
                                                        }
                                                    >
                                                        <SelectTrigger
                                                            id="status"
                                                            className="w-full"
                                                        >
                                                            <SelectValue placeholder="Selecione" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {statuses.map(
                                                                (option) => (
                                                                    <SelectItem
                                                                        key={
                                                                            option.value
                                                                        }
                                                                        value={
                                                                            option.value
                                                                        }
                                                                    >
                                                                        {
                                                                            option.label
                                                                        }
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError
                                                        message={errors.status}
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="status_notes">
                                                        Observações
                                                    </Label>
                                                    <Textarea
                                                        id="status_notes"
                                                        name="notes"
                                                    />
                                                    <InputError
                                                        message={errors.notes}
                                                    />
                                                </div>
                                                <DialogFooter className="gap-2">
                                                    <DialogClose asChild>
                                                        <Button variant="secondary">
                                                            Cancelar
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {processing && (
                                                            <Spinner />
                                                        )}
                                                        Confirmar
                                                    </Button>
                                                </DialogFooter>
                                            </div>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    )}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Documentos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <LeaseDocuments
                            leaseId={lease.id}
                            documents={documents}
                            canManage={canEdit}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Linha do tempo do contrato</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {events.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhum evento registrado ainda.
                            </p>
                        ) : (
                            <ol className="flex flex-col gap-4">
                                {events.map((event) => (
                                    <li
                                        key={event.id}
                                        className="flex flex-col gap-1 border-l-2 border-muted pl-4"
                                    >
                                        <div className="flex items-center gap-2">
                                            <Badge variant="outline">
                                                {event.type_label}
                                            </Badge>
                                            <span className="text-sm text-muted-foreground">
                                                {formatDate(event.occurred_on)}
                                            </span>
                                        </div>
                                        <p className="text-sm">
                                            {event.description}
                                        </p>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </CardContent>
                </Card>
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
        title: 'Detalhes',
        href: '',
    },
];

LeasesShow.layout = {
    breadcrumbs,
};
