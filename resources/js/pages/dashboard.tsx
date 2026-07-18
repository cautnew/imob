import { Head, Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowDownCircle,
    ArrowUpCircle,
    Wallet,
} from 'lucide-react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { dashboard } from '@/routes';
import { edit as editTransaction } from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';

type Summary = {
    total_income: string;
    total_expense: string;
    balance: string;
    pending_count: number;
    pending_amount: string;
    overdue_count: number;
    overdue_amount: string;
};

type MonthlyPoint = {
    month: string;
    income: string;
    expense: string;
};

type CategoryPoint = {
    category: string;
    amount: string;
};

type UpcomingTransaction = {
    id: number;
    description: string;
    due_date: string;
    amount: string;
    status: string;
    property: string;
};

type Props = {
    selectedMonth: string;
    monthOptions: string[];
    summary: Summary;
    monthlySeries: MonthlyPoint[];
    categoryBreakdown: CategoryPoint[];
    upcoming: UpcomingTransaction[];
};

const formatCurrency = (value: string | number) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const monthLabel = (value: string) =>
    new Intl.DateTimeFormat('pt-BR', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${value}-01T00:00:00`));

const statusLabel = (status: string) =>
    ({ pendente: 'Pendente', pago: 'Pago', vencido: 'Vencido' })[status] ??
    status;

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

export default function Dashboard({
    selectedMonth,
    monthOptions,
    summary,
    monthlySeries,
    categoryBreakdown,
    upcoming,
}: Props) {
    const chartData = monthlySeries.map((point) => ({
        month: monthLabel(point.month),
        Receitas: Number(point.income),
        Despesas: Number(point.expense),
    }));

    const categoryData = categoryBreakdown.map((point) => ({
        category: point.category,
        Valor: Number(point.amount),
    }));

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold tracking-tight">
                        Dashboard financeiro
                    </h2>
                    <Select
                        value={selectedMonth}
                        onValueChange={(value) =>
                            router.get(
                                dashboard({ query: { month: value } }).url,
                                {},
                                { preserveState: true, preserveScroll: true },
                            )
                        }
                    >
                        <SelectTrigger className="w-56">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {monthOptions.map((option) => (
                                <SelectItem key={option} value={option}>
                                    {monthLabel(option)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Receitas do mês
                            </CardTitle>
                            <ArrowUpCircle className="size-4 text-chart-1" />
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-semibold">
                                {formatCurrency(summary.total_income)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Despesas do mês
                            </CardTitle>
                            <ArrowDownCircle className="size-4 text-chart-2" />
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-semibold">
                                {formatCurrency(summary.total_expense)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Saldo do mês
                            </CardTitle>
                            <Wallet className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-semibold">
                                {formatCurrency(summary.balance)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Vencidos
                            </CardTitle>
                            <AlertTriangle className="size-4 text-destructive" />
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-semibold">
                                {summary.overdue_count}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {formatCurrency(summary.overdue_amount)}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Receitas x despesas (6 meses)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={chartData}>
                                        <CartesianGrid
                                            strokeDasharray="3 3"
                                            className="stroke-border"
                                        />
                                        <XAxis
                                            dataKey="month"
                                            tick={{ fontSize: 12 }}
                                        />
                                        <YAxis
                                            tick={{ fontSize: 12 }}
                                            tickFormatter={(value) =>
                                                formatCurrency(value)
                                            }
                                            width={90}
                                        />
                                        <Tooltip
                                            formatter={(value) =>
                                                formatCurrency(Number(value))
                                            }
                                        />
                                        <Legend />
                                        <Bar
                                            dataKey="Receitas"
                                            fill="var(--chart-1)"
                                            radius={[4, 4, 0, 0]}
                                        />
                                        <Bar
                                            dataKey="Despesas"
                                            fill="var(--chart-2)"
                                            radius={[4, 4, 0, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Despesas por categoria</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {categoryData.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Nenhuma despesa lançada neste mês.
                                </p>
                            ) : (
                                <div className="h-72">
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <BarChart
                                            data={categoryData}
                                            layout="vertical"
                                            margin={{ left: 16 }}
                                        >
                                            <CartesianGrid
                                                strokeDasharray="3 3"
                                                className="stroke-border"
                                            />
                                            <XAxis
                                                type="number"
                                                tick={{ fontSize: 12 }}
                                                tickFormatter={(value) =>
                                                    formatCurrency(value)
                                                }
                                            />
                                            <YAxis
                                                type="category"
                                                dataKey="category"
                                                tick={{ fontSize: 12 }}
                                                width={100}
                                            />
                                            <Tooltip
                                                formatter={(value) =>
                                                    formatCurrency(
                                                        Number(value),
                                                    )
                                                }
                                            />
                                            <Bar
                                                dataKey="Valor"
                                                fill="var(--chart-3)"
                                                radius={[0, 4, 4, 0]}
                                            />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Próximos vencimentos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {upcoming.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhum lançamento pendente.
                            </p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead>Imóvel</TableHead>
                                        <TableHead>Vencimento</TableHead>
                                        <TableHead>Valor</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {upcoming.map((transaction) => (
                                        <TableRow key={transaction.id}>
                                            <TableCell className="font-medium">
                                                <Link
                                                    href={editTransaction(
                                                        transaction.id,
                                                    )}
                                                    className="hover:underline"
                                                >
                                                    {transaction.description}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {transaction.property}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {formatDate(
                                                    transaction.due_date,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(
                                                    transaction.amount,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={statusBadgeVariant(
                                                        transaction.status,
                                                    )}
                                                >
                                                    {statusLabel(
                                                        transaction.status,
                                                    )}
                                                </Badge>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

Dashboard.layout = {
    breadcrumbs,
};
