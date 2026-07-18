import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { show as showBill } from '@/routes/portal/bills';
import { show as showLease } from '@/routes/portal/leases';

type LeaseRow = {
    id: number;
    status: string;
    status_label: string;
    property: { id: number; title: string };
    rent_amount: string;
};

type UpcomingBillRow = {
    id: number;
    due_date: string;
    status: string;
    status_label: string;
    total_amount: string;
    property: { id: number; title: string };
};

type RecentPaymentRow = {
    id: number;
    description: string;
    amount: string;
    paid_date: string | null;
};

type Props = {
    leases: LeaseRow[];
    upcomingBills: UpcomingBillRow[];
    recentPayments: RecentPaymentRow[];
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

export default function PortalDashboard({
    leases,
    upcomingBills,
    recentPayments,
}: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title="Dashboard"
                    description="Acompanhe seus contratos, boletos e pagamentos"
                />

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Meus contratos</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {leases.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Nenhum contrato encontrado.
                                </p>
                            ) : (
                                leases.map((lease) => (
                                    <Link
                                        key={lease.id}
                                        href={showLease(lease.id)}
                                        className="flex items-center justify-between gap-2 rounded-md border p-3 hover:bg-accent"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {lease.property.title}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatCurrency(
                                                    lease.rent_amount,
                                                )}
                                                /mês
                                            </p>
                                        </div>
                                        <Badge variant="outline">
                                            {lease.status_label}
                                        </Badge>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Próximos boletos</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {upcomingBills.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Nenhum boleto em aberto.
                                </p>
                            ) : (
                                upcomingBills.map((bill) => (
                                    <Link
                                        key={bill.id}
                                        href={showBill(bill.id)}
                                        className="flex items-center justify-between gap-2 rounded-md border p-3 hover:bg-accent"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {bill.property.title}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                Vence em{' '}
                                                {formatDate(bill.due_date)}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-medium">
                                                {formatCurrency(
                                                    bill.total_amount,
                                                )}
                                            </p>
                                            <Badge variant="outline">
                                                {bill.status_label}
                                            </Badge>
                                        </div>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Pagamentos recentes</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {recentPayments.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Nenhum pagamento registrado ainda.
                                </p>
                            ) : (
                                recentPayments.map((payment) => (
                                    <div
                                        key={payment.id}
                                        className="flex items-center justify-between gap-2 rounded-md border p-3"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {payment.description}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {payment.paid_date &&
                                                    formatDate(
                                                        payment.paid_date,
                                                    )}
                                            </p>
                                        </div>
                                        <p className="font-medium">
                                            {formatCurrency(payment.amount)}
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
