import { Form, Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, Pencil, Plus, RotateCcw, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/hooks/use-permissions';
import { create, destroy, edit, index } from '@/routes/transactions';
import { toggle as toggleStatus } from '@/routes/transactions/status';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type TransactionRow = {
    id: number;
    description: string;
    amount: string;
    due_date: string;
    status: string;
    property: { id: number; title: string };
    transaction_category: { id: number; name: string; type: string };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
};

type Props = {
    transactions: Paginated<TransactionRow>;
    filters: {
        type: string | null;
        status: string | null;
        property_id: number | null;
        search: string | null;
    };
    types: Option[];
    statuses: Option[];
    properties: { id: number; title: string }[];
};

const ALL = 'all';

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

export default function TransactionsIndex({
    transactions,
    filters,
    types,
    statuses,
    properties,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('financeiro.criar');
    const canEdit = can('financeiro.editar');
    const canDelete = can('financeiro.excluir');

    const typeLabel = (type: string) =>
        types.find((option) => option.value === type)?.label ?? type;

    const statusLabel = (status: string) =>
        statuses.find((option) => option.value === status)?.label ?? status;

    const updateFilters = (next: Partial<Props['filters']>) => {
        router.get(
            index({
                query: {
                    ...(filters.type ? { type: filters.type } : {}),
                    ...(filters.status ? { status: filters.status } : {}),
                    ...(filters.property_id
                        ? { property_id: filters.property_id }
                        : {}),
                    ...(filters.search ? { search: filters.search } : {}),
                    ...next,
                },
            }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const handleSearchSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const search = new FormData(event.currentTarget)
            .get('search')
            ?.toString();
        updateFilters({ search: search || undefined });
    };

    return (
        <>
            <Head title="Financeiro" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Financeiro"
                        description="Lançamentos de receitas e despesas da sua imobiliária"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo lançamento
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <form
                        onSubmit={handleSearchSubmit}
                        className="flex items-center gap-2"
                    >
                        <Input
                            type="search"
                            name="search"
                            defaultValue={filters.search ?? ''}
                            placeholder="Buscar por descrição"
                            className="w-64"
                        />
                        <Button type="submit" variant="outline">
                            Buscar
                        </Button>
                    </form>

                    <Select
                        value={filters.type ?? ALL}
                        onValueChange={(value) =>
                            updateFilters({
                                type: value === ALL ? undefined : value,
                            })
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="Tipo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>Todos os tipos</SelectItem>
                            {types.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={filters.status ?? ALL}
                        onValueChange={(value) =>
                            updateFilters({
                                status: value === ALL ? undefined : value,
                            })
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>Todos os status</SelectItem>
                            {statuses.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={
                            filters.property_id
                                ? String(filters.property_id)
                                : ALL
                        }
                        onValueChange={(value) =>
                            updateFilters({
                                property_id:
                                    value === ALL ? undefined : Number(value),
                            })
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Imóvel" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                Todos os imóveis
                            </SelectItem>
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
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Imóvel</TableHead>
                            <TableHead>Categoria</TableHead>
                            <TableHead>Vencimento</TableHead>
                            <TableHead>Valor</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {transactions.data.map((transaction) => (
                            <TableRow key={transaction.id}>
                                <TableCell className="font-medium">
                                    {transaction.description}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {transaction.property.title}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {transaction.transaction_category.name}{' '}
                                    <span className="text-xs">
                                        (
                                        {typeLabel(
                                            transaction.transaction_category
                                                .type,
                                        )}
                                        )
                                    </span>
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
                                        {statusLabel(transaction.status)}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Form
                                                action={toggleStatus(
                                                    transaction.id,
                                                )}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {transaction.status ===
                                                        'pago' ? (
                                                            <RotateCcw />
                                                        ) : (
                                                            <CheckCircle2 />
                                                        )}
                                                        <span className="sr-only">
                                                            {transaction.status ===
                                                            'pago'
                                                                ? 'Reabrir'
                                                                : 'Marcar como pago'}
                                                        </span>
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                        {canEdit && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={edit(transaction.id)}
                                                >
                                                    <Pencil />
                                                    <span className="sr-only">
                                                        Editar
                                                    </span>
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                    >
                                                        <Trash2 />
                                                        <span className="sr-only">
                                                            Excluir
                                                        </span>
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogTitle>
                                                        Excluir{' '}
                                                        {
                                                            transaction.description
                                                        }
                                                        ?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            transaction.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.transaction
                                                                    }
                                                                />
                                                                <DialogFooter className="gap-2">
                                                                    <DialogClose
                                                                        asChild
                                                                    >
                                                                        <Button variant="secondary">
                                                                            Cancelar
                                                                        </Button>
                                                                    </DialogClose>
                                                                    <Button
                                                                        variant="destructive"
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
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
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>

                {transactions.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {transactions.links.map((link, index) => (
                            <Button
                                key={`${link.label}-${index}`}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                asChild={!!link.url}
                            >
                                {link.url ? (
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Financeiro',
        href: index(),
    },
];

TransactionsIndex.layout = {
    breadcrumbs,
};
