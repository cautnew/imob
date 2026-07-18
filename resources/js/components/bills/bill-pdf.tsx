import { router } from '@inertiajs/react';
import { Download, FileText, UploadCloud } from 'lucide-react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { download } from '@/routes/bills';
import { store as uploadPdf } from '@/routes/bills/pdf';

type Props = {
    billId: number;
    hasPdf: boolean;
    originalFilename: string | null;
    canManage: boolean;
};

export default function BillPdf({
    billId,
    hasPdf,
    originalFilename,
    canManage,
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
            uploadPdf(billId).url,
            { file },
            {
                forceFormData: true,
                preserveScroll: true,
                only: ['bill', 'events'],
                onFinish: () => {
                    setUploading(false);
                    event.target.value = '';
                },
            },
        );
    };

    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            {hasPdf ? (
                <a
                    href={download(billId).url}
                    className="flex items-center gap-2 text-sm hover:underline"
                >
                    <FileText className="size-4 shrink-0 text-muted-foreground" />
                    <span className="font-medium">
                        {originalFilename ?? 'boleto.pdf'}
                    </span>
                </a>
            ) : (
                <p className="text-sm text-muted-foreground">
                    Nenhum PDF anexado ainda.
                </p>
            )}

            <div className="flex items-center gap-2">
                {hasPdf && (
                    <Button variant="outline" size="sm" asChild>
                        <a href={download(billId).url}>
                            <Download />
                            Baixar
                        </a>
                    </Button>
                )}
                {canManage && (
                    <>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            disabled={uploading}
                            onClick={() => fileInputRef.current?.click()}
                        >
                            {uploading ? <Spinner /> : <UploadCloud />}
                            {hasPdf ? 'Substituir PDF' : 'Anexar PDF'}
                        </Button>
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="application/pdf"
                            className="hidden"
                            onChange={handleFileChange}
                        />
                    </>
                )}
            </div>
        </div>
    );
}
