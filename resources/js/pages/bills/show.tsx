import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import BillPdf from '@/components/bills/bill-pdf';
import BillTransactions from '@/components/bills/bill-transactions';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { usePermissions } from '@/hooks/use-permissions';
import { destroy, edit, index } from '@/routes/bills';
import { update as updateStatus } from '@/routes/bills/status';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type BillDetail = {
    id: number;
    due_date: string;
    paid_date: string | null;
    description: string | null;
    status: string;
    total_amount: string;
    has_pdf: boolean;
    original_filename: string | null;
    lease: {
        id: number;
        property: { id: number; title: string };
        lessee: { id: number; name: string };
    };
};

type TransactionRow = {
    id: number;
    description: string;
    amount: string;
    due_date: string;
    status: string;
    transaction_category: { id: number; name: string };
};

type AvailableTransaction = {
    id: number;
    description: string;
    amount: string;
    due_date: string;
};

type BillEventRow = {
    id: number;
    type: string;
    type_label: string;
    occurred_on: string;
    description: string;
};

type Props = {
    bill: BillDetail;
    transactions: TransactionRow[];
    availableTransactions: AvailableTransaction[];
    events: BillEventRow[];
    statuses: Option[];
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

const statusBadgeVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (status === 'pago') {
        return 'secondary';
    }

    if (status === 'vencido') {
        return 'destructive';
    }

    return 'outline';
};

export default function BillsShow({
    bill,
    transactions,
    availableTransactions,
    events,
    statuses,
}: Props) {
    const { can } = usePermissions();

    const canEdit = can('boletos.editar');
    const canDelete = can('boletos.excluir');

    const [status, setStatus] = useState(
        bill.status === 'vencido' ? 'pendente' : bill.status,
    );

    const statusLabel = (value: string) =>
        statuses.find((option) => option.value === value)?.label ?? value;

    return (
        <>
            <Head title={`Boleto — ${bill.lease.property.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={`Boleto — ${bill.lease.property.title}`}
                        description={bill.lease.lessee.name}
                    />
                    <div className="flex items-center gap-2">
                        {canEdit && (
                            <Button variant="outline" asChild>
                                <Link href={edit(bill.id)}>
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
                                        Excluir boleto de{' '}
                                        {bill.lease.property.title}?
                                    </DialogTitle>
                                    <DialogDescription>
                                        Essa ação não pode ser desfeita. Os
                                        lançamentos vinculados não serão
                                        excluídos.
                                    </DialogDescription>
                                    <Form action={destroy(bill.id)}>
                                        {({ processing, errors }) => (
                                            <>
                                                <InputError
                                                    message={errors.bill}
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
                            <CardTitle>Detalhes do boleto</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Situação
                                </p>
                                <Badge
                                    variant={statusBadgeVariant(bill.status)}
                                >
                                    {statusLabel(bill.status)}
                                </Badge>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Locação
                                </p>
                                <p>
                                    {bill.lease.property.title} —{' '}
                                    {bill.lease.lessee.name}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Vencimento
                                </p>
                                <p>{formatDate(bill.due_date)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Pagamento
                                </p>
                                <p>
                                    {bill.paid_date
                                        ? formatDate(bill.paid_date)
                                        : '—'}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Valor total
                                </p>
                                <p>{formatCurrency(bill.total_amount)}</p>
                            </div>
                            {bill.description && (
                                <div className="sm:col-span-2">
                                    <p className="text-sm text-muted-foreground">
                                        Descrição
                                    </p>
                                    <p>{bill.description}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {canEdit && (
                        <div className="flex flex-col gap-4">
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        Alterar situação
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Alterar situação</DialogTitle>
                                    <DialogDescription>
                                        Ao marcar como pago, todos os
                                        lançamentos vinculados também serão
                                        marcados como pagos. Ao reabrir, os
                                        lançamentos voltam a ficar pendentes.
                                    </DialogDescription>
                                    <Form action={updateStatus(bill.id)}>
                                        {({ processing, errors }) => (
                                            <div className="grid gap-4">
                                                <div className="grid gap-2">
                                                    <Select
                                                        name="status"
                                                        value={status}
                                                        onValueChange={
                                                            setStatus
                                                        }
                                                    >
                                                        <SelectTrigger className="w-full">
                                                            <SelectValue placeholder="Selecione" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {statuses
                                                                .filter(
                                                                    (option) =>
                                                                        option.value !==
                                                                        'vencido',
                                                                )
                                                                .map(
                                                                    (
                                                                        option,
                                                                    ) => (
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
                        <CardTitle>PDF do boleto</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BillPdf
                            billId={bill.id}
                            hasPdf={bill.has_pdf}
                            originalFilename={bill.original_filename}
                            canManage={canEdit}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lançamentos vinculados</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BillTransactions
                            billId={bill.id}
                            transactions={transactions}
                            availableTransactions={availableTransactions}
                            canManage={canEdit}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Histórico do boleto</CardTitle>
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
        title: 'Boletos',
        href: index(),
    },
    {
        title: 'Detalhes',
        href: '',
    },
];

BillsShow.layout = {
    breadcrumbs,
};
