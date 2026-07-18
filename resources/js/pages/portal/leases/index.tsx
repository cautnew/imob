import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { show } from '@/routes/portal/leases';

type LeaseRow = {
    id: number;
    status: string;
    status_label: string;
    start_date: string;
    end_date: string;
    rent_amount: string;
    property: { id: number; title: string; city: string; state: string };
};

type Props = {
    leases: LeaseRow[];
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

export default function PortalLeasesIndex({ leases }: Props) {
    return (
        <>
            <Head title="Meus contratos" />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title="Meus contratos"
                    description="Contratos de locação vinculados ao seu cadastro"
                />

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Imóvel</TableHead>
                            <TableHead>Início</TableHead>
                            <TableHead>Fim</TableHead>
                            <TableHead>Aluguel</TableHead>
                            <TableHead>Situação</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {leases.map((lease) => (
                            <TableRow key={lease.id}>
                                <TableCell className="font-medium">
                                    <Link
                                        href={show(lease.id)}
                                        className="hover:underline"
                                    >
                                        {lease.property.title}
                                    </Link>
                                    <p className="text-sm text-muted-foreground">
                                        {lease.property.city}/
                                        {lease.property.state}
                                    </p>
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {formatDate(lease.start_date)}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {formatDate(lease.end_date)}
                                </TableCell>
                                <TableCell>
                                    {formatCurrency(lease.rent_amount)}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {lease.status_label}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}
