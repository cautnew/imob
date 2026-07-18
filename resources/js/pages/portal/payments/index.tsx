import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type PaymentRow = {
    id: number;
    description: string;
    amount: string;
    paid_date: string | null;
    transaction_category: { id: number; name: string };
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
    payments: Paginated<PaymentRow>;
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

export default function PortalPaymentsIndex({ payments }: Props) {
    return (
        <>
            <Head title="Meus pagamentos" />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title="Meus pagamentos"
                    description="Histórico de pagamentos confirmados"
                />

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Categoria</TableHead>
                            <TableHead>Data do pagamento</TableHead>
                            <TableHead>Valor</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {payments.data.length === 0 ? (
                            <TableRow>
                                <TableCell
                                    colSpan={4}
                                    className="text-center text-muted-foreground"
                                >
                                    Nenhum pagamento registrado ainda.
                                </TableCell>
                            </TableRow>
                        ) : (
                            payments.data.map((payment) => (
                                <TableRow key={payment.id}>
                                    <TableCell className="font-medium">
                                        {payment.description}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {payment.transaction_category.name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {payment.paid_date
                                            ? formatDate(payment.paid_date)
                                            : '—'}
                                    </TableCell>
                                    <TableCell>
                                        {formatCurrency(payment.amount)}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>

                {payments.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {payments.links.map((link, index) => (
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
