import { Head, router } from '@inertiajs/react';
import { UploadCloud } from 'lucide-react';
import { useRef, useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { store as storeReceipt } from '@/routes/portal/bill-receipts';
import { download } from '@/routes/portal/bills';

type BillDetail = {
    id: number;
    due_date: string;
    paid_date: string | null;
    description: string | null;
    status: string;
    status_label: string;
    total_amount: string;
    has_pdf: boolean;
    property: { id: number; title: string };
};

type TransactionRow = {
    id: number;
    description: string;
    amount: string;
    due_date: string;
    status: string;
    transaction_category: { id: number; name: string };
};

type ReceiptRow = {
    id: number;
    status: string;
    status_label: string;
    original_filename: string | null;
    rejection_reason: string | null;
    created_at: string;
};

type Props = {
    bill: BillDetail;
    transactions: TransactionRow[];
    receipts: ReceiptRow[];
    canUploadReceipt: boolean;
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

export default function PortalBillsShow({
    bill,
    transactions,
    receipts,
    canUploadReceipt,
}: Props) {
    const [uploading, setUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        setUploading(true);

        router.post(
            storeReceipt(bill.id).url,
            { file },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => {
                    setUploading(false);
                    event.target.value = '';
                },
            },
        );
    };

    return (
        <>
            <Head title={`Boleto — ${bill.property.title}`} />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6">
                <Heading
                    title={`Boleto — ${bill.property.title}`}
                    description={bill.description ?? undefined}
                />

                {bill.status === 'aguardando_aprovacao' && (
                    <div className="rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                        Comprovante enviado. Aguarde a aprovação da imobiliária
                        para a confirmação do pagamento.
                    </div>
                )}

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
                                    {bill.status_label}
                                </Badge>
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
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-4">
                        {bill.has_pdf && (
                            <Button variant="outline" asChild>
                                <a href={download(bill.id).url}>
                                    Baixar boleto
                                </a>
                            </Button>
                        )}

                        {canUploadReceipt ? (
                            <>
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    className="hidden"
                                    onChange={handleFileChange}
                                />
                                <Button
                                    disabled={uploading}
                                    onClick={() =>
                                        fileInputRef.current?.click()
                                    }
                                >
                                    {uploading ? <Spinner /> : <UploadCloud />}
                                    Enviar comprovante
                                </Button>
                            </>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Já existe um comprovante aguardando aprovação
                                para este boleto.
                            </p>
                        )}
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Detalhamento da cobrança</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-2">
                        {transactions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhum lançamento vinculado.
                            </p>
                        ) : (
                            transactions.map((transaction) => (
                                <div
                                    key={transaction.id}
                                    className="flex items-center justify-between border-b py-2 last:border-b-0"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {transaction.description}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {
                                                transaction.transaction_category
                                                    .name
                                            }{' '}
                                            — vence em{' '}
                                            {formatDate(transaction.due_date)}
                                        </p>
                                    </div>
                                    <p className="font-medium">
                                        {formatCurrency(transaction.amount)}
                                    </p>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Comprovantes enviados</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {receipts.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhum comprovante enviado ainda.
                            </p>
                        ) : (
                            <ol className="flex flex-col gap-4">
                                {receipts.map((receipt) => (
                                    <li
                                        key={receipt.id}
                                        className="flex flex-col gap-1 border-l-2 border-muted pl-4"
                                    >
                                        <div className="flex items-center gap-2">
                                            <Badge variant="outline">
                                                {receipt.status_label}
                                            </Badge>
                                            <span className="text-sm text-muted-foreground">
                                                {formatDate(receipt.created_at)}
                                            </span>
                                        </div>
                                        {receipt.original_filename && (
                                            <p className="text-sm">
                                                {receipt.original_filename}
                                            </p>
                                        )}
                                        {receipt.rejection_reason && (
                                            <p className="text-sm text-destructive">
                                                Motivo:{' '}
                                                {receipt.rejection_reason}
                                            </p>
                                        )}
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
