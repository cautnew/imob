import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { show } from '@/routes/portal/bills';

type BillRow = {
    id: number;
    description: string | null;
    total_amount: string;
    due_date: string;
    status: string;
    status_label: string;
    property: { id: number; title: string };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    last_page: number;
};

type Props = {
    bills: Paginated<BillRow>;
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

export default function PortalBillsIndex({ bills }: Props) {
    return (
        <>
            <Head title="Meus boletos" />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title="Meus boletos"
                    description="Cobranças vinculadas aos seus contratos"
                />

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
                                        {bill.property.title}
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
                                        {bill.status_label}
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
