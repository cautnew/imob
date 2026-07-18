import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type LeaseDetail = {
    id: number;
    status: string;
    status_label: string;
    start_date: string;
    end_date: string;
    rent_amount: string;
    adjustment_index: string;
    adjustment_interval_months: number;
    last_adjustment_date: string | null;
    renewal_type: string;
    notes: string | null;
    property: { id: number; title: string; city: string; state: string };
    owner: { id: number; name: string };
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
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatCurrency = (value: string) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));

export default function PortalLeasesShow({ lease, events }: Props) {
    return (
        <>
            <Head title={`Contrato — ${lease.property.title}`} />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title={`Contrato — ${lease.property.title}`}
                    description={`Proprietário: ${lease.owner.name}`}
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Detalhes do contrato</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                Situação
                            </p>
                            <Badge variant="outline">
                                {lease.status_label}
                            </Badge>
                        </div>
                        <div>
                            <p className="text-sm text-muted-foreground">
                                Imóvel
                            </p>
                            <p>
                                {lease.property.title} — {lease.property.city}/
                                {lease.property.state}
                            </p>
                        </div>
                        <div>
                            <p className="text-sm text-muted-foreground">
                                Início
                            </p>
                            <p>{formatDate(lease.start_date)}</p>
                        </div>
                        <div>
                            <p className="text-sm text-muted-foreground">Fim</p>
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
                                A cada {lease.adjustment_interval_months} meses
                                {lease.last_adjustment_date &&
                                    ` (último em ${formatDate(lease.last_adjustment_date)})`}
                            </p>
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

                <Card>
                    <CardHeader>
                        <CardTitle>Histórico do contrato</CardTitle>
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
