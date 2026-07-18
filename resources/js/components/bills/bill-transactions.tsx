import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { destroy, store } from '@/routes/bill-transactions';

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

type Props = {
    billId: number;
    transactions: TransactionRow[];
    availableTransactions: AvailableTransaction[];
    canManage: boolean;
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

export default function BillTransactions({
    billId,
    transactions,
    availableTransactions,
    canManage,
}: Props) {
    const [selected, setSelected] = useState('');
    const [attaching, setAttaching] = useState(false);
    const [pendingRemove, setPendingRemove] = useState<TransactionRow | null>(
        null,
    );

    const total = transactions.reduce(
        (sum, transaction) => sum + Number(transaction.amount),
        0,
    );

    const handleAttach = () => {
        if (!selected) {
            return;
        }

        setAttaching(true);

        router.post(
            store(billId).url,
            { transaction_id: Number(selected) },
            {
                preserveScroll: true,
                only: [
                    'transactions',
                    'availableTransactions',
                    'events',
                    'bill',
                ],
                onFinish: () => {
                    setAttaching(false);
                    setSelected('');
                },
            },
        );
    };

    const confirmRemove = () => {
        if (!pendingRemove) {
            return;
        }

        router.delete(destroy([billId, pendingRemove.id]).url, {
            preserveScroll: true,
            only: ['transactions', 'availableTransactions', 'events', 'bill'],
            onSuccess: () => setPendingRemove(null),
        });
    };

    return (
        <div className="flex flex-col gap-4">
            {canManage && (
                <div className="flex flex-wrap items-end gap-2">
                    <div className="grid flex-1 gap-2">
                        <Select value={selected} onValueChange={setSelected}>
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Selecione um lançamento da locação" />
                            </SelectTrigger>
                            <SelectContent>
                                {availableTransactions.map((transaction) => (
                                    <SelectItem
                                        key={transaction.id}
                                        value={String(transaction.id)}
                                    >
                                        {transaction.description} —{' '}
                                        {formatCurrency(transaction.amount)}{' '}
                                        (venc.{' '}
                                        {formatDate(transaction.due_date)})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={!selected || attaching}
                        onClick={handleAttach}
                    >
                        Vincular lançamento
                    </Button>
                </div>
            )}

            {transactions.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    Nenhum lançamento vinculado ainda.
                </p>
            ) : (
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Categoria</TableHead>
                            <TableHead>Vencimento</TableHead>
                            <TableHead>Valor</TableHead>
                            <TableHead>Status</TableHead>
                            {canManage && (
                                <TableHead className="w-0">Ações</TableHead>
                            )}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {transactions.map((transaction) => (
                            <TableRow key={transaction.id}>
                                <TableCell className="font-medium">
                                    {transaction.description}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {transaction.transaction_category.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {formatDate(transaction.due_date)}
                                </TableCell>
                                <TableCell>
                                    {formatCurrency(transaction.amount)}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant={statusBadgeVariant(
                                            transaction.status,
                                        )}
                                    >
                                        {transaction.status}
                                    </Badge>
                                </TableCell>
                                {canManage && (
                                    <TableCell>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                setPendingRemove(transaction)
                                            }
                                        >
                                            <Trash2 />
                                            <span className="sr-only">
                                                Desvincular
                                            </span>
                                        </Button>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                    <TableFooter>
                        <TableRow>
                            <TableCell colSpan={3} className="font-medium">
                                Total
                            </TableCell>
                            <TableCell className="font-medium">
                                {formatCurrency(String(total))}
                            </TableCell>
                            <TableCell />
                            {canManage && <TableCell />}
                        </TableRow>
                    </TableFooter>
                </Table>
            )}

            <Dialog
                open={pendingRemove !== null}
                onOpenChange={(open) => !open && setPendingRemove(null)}
            >
                <DialogContent>
                    <DialogTitle>
                        Desvincular {pendingRemove?.description}?
                    </DialogTitle>
                    <DialogDescription>
                        O lançamento continuará existindo no Financeiro, apenas
                        deixará de compor este boleto.
                    </DialogDescription>
                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary">Cancelar</Button>
                        </DialogClose>
                        <Button variant="destructive" onClick={confirmRemove}>
                            Desvincular
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
