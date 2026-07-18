import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { create, index, show } from '@/routes/bills';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type BillRow = {
    id: number;
    description: string | null;
    total_amount: string;
    due_date: string;
    status: string;
    lease: {
        id: number;
        property: { id: number; title: string };
    };
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
    bills: Paginated<BillRow>;
    filters: {
        status: string | null;
        lease_id: number | null;
        search: string | null;
    };
    statuses: Option[];
    leases: { id: number; property: { id: number; title: string } | null }[];
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

export default function BillsIndex({
    bills,
    filters,
    statuses,
    leases,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('boletos.criar');

    const statusLabel = (status: string) =>
        statuses.find((option) => option.value === status)?.label ?? status;

    const updateFilters = (next: Partial<Props['filters']>) => {
        router.get(
            index({
                query: {
                    ...(filters.status ? { status: filters.status } : {}),
                    ...(filters.lease_id ? { lease_id: filters.lease_id } : {}),
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
            <Head title="Boletos" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Boletos"
                        description="Cobranças vinculadas às locações"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo boleto
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
                            placeholder="Buscar por imóvel"
                            className="w-64"
                        />
                        <Button type="submit" variant="outline">
                            Buscar
                        </Button>
                    </form>

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
                            filters.lease_id ? String(filters.lease_id) : ALL
                        }
                        onValueChange={(value) =>
                            updateFilters({
                                lease_id:
                                    value === ALL ? undefined : Number(value),
                            })
                        }
                    >
                        <SelectTrigger className="w-56">
                            <SelectValue placeholder="Locação" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                Todas as locações
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
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Imóvel</TableHead>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Vencimento</TableHead>
                            <TableHead>Valor total</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {bills.data.map((bill) => (
                            <TableRow key={bill.id}>
                                <TableCell className="font-medium">
                                    <Link
                                        href={show(bill.id)}
                                        className="hover:underline"
                                    >
                                        {bill.lease.property.title}
                                    </Link>
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {bill.description ?? '—'}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {formatDate(bill.due_date)}
                                </TableCell>
                                <TableCell>
                                    {formatCurrency(bill.total_amount)}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant={statusBadgeVariant(
                                            bill.status,
                                        )}
                                    >
                                        {statusLabel(bill.status)}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>

                {bills.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {bills.links.map((link, index) => (
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
        title: 'Boletos',
        href: index(),
    },
];

BillsIndex.layout = {
    breadcrumbs,
};
