import { router } from '@inertiajs/react';
import { FileText, Trash2, UploadCloud } from 'lucide-react';
import { useRef, useState } from 'react';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { destroy, store } from '@/routes/lease-documents';

export type LeaseDocumentItem = {
    id: number;
    name: string;
    url: string;
    original_filename: string | null;
    size: number | null;
    created_at: string;
};

type Props = {
    leaseId: number;
    documents: LeaseDocumentItem[];
    canManage: boolean;
};

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');

const formatSize = (bytes: number | null) => {
    if (!bytes) {
        return null;
    }

    return `${(bytes / 1024).toFixed(0)} KB`;
};

export default function LeaseDocuments({
    leaseId,
    documents,
    canManage,
}: Props) {
    const [name, setName] = useState('');
    const [uploading, setUploading] = useState(false);
    const [pendingDelete, setPendingDelete] =
        useState<LeaseDocumentItem | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        if (!name.trim()) {
            event.target.value = '';

            return;
        }

        setUploading(true);

        router.post(
            store(leaseId).url,
            { name, file },
            {
                forceFormData: true,
                preserveScroll: true,
                only: ['documents', 'events'],
                onFinish: () => {
                    setUploading(false);
                    setName('');
                    event.target.value = '';
                },
            },
        );
    };

    const confirmDelete = () => {
        if (!pendingDelete) {
            return;
        }

        router.delete(destroy([leaseId, pendingDelete.id]).url, {
            preserveScroll: true,
            only: ['documents', 'events'],
            onSuccess: () => setPendingDelete(null),
        });
    };

    return (
        <div className="flex flex-col gap-4">
            {canManage && (
                <div className="flex flex-wrap items-end gap-2">
                    <div className="grid flex-1 gap-2">
                        <Input
                            placeholder="Nome do documento"
                            value={name}
                            onChange={(event) => setName(event.target.value)}
                        />
                    </div>
                    <button
                        type="button"
                        disabled={!name.trim() || uploading}
                        onClick={() => fileInputRef.current?.click()}
                        className="inline-flex h-9 items-center justify-center gap-2 rounded-md border px-4 text-sm font-medium hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {uploading ? (
                            <Spinner className="size-4" />
                        ) : (
                            <UploadCloud className="size-4" />
                        )}
                        Anexar documento
                    </button>
                    <input
                        ref={fileInputRef}
                        type="file"
                        className="hidden"
                        onChange={handleFileChange}
                    />
                </div>
            )}

            {documents.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    Nenhum documento anexado ainda.
                </p>
            ) : (
                <ul className="flex flex-col gap-2">
                    {documents.map((document) => (
                        <li
                            key={document.id}
                            className="flex items-center justify-between gap-2 rounded-md border p-3"
                        >
                            <a
                                href={document.url}
                                target="_blank"
                                rel="noreferrer"
                                className="flex min-w-0 items-center gap-2 text-sm hover:underline"
                            >
                                <FileText className="size-4 shrink-0 text-muted-foreground" />
                                <span className="truncate font-medium">
                                    {document.name}
                                </span>
                                <span className="shrink-0 text-xs text-muted-foreground">
                                    {formatDate(document.created_at)}
                                    {formatSize(document.size) &&
                                        ` — ${formatSize(document.size)}`}
                                </span>
                            </a>
                            {canManage && (
                                <button
                                    type="button"
                                    onClick={() => setPendingDelete(document)}
                                    className="shrink-0 text-muted-foreground hover:text-destructive"
                                >
                                    <Trash2 className="size-4" />
                                    <span className="sr-only">Remover</span>
                                </button>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            <Dialog
                open={pendingDelete !== null}
                onOpenChange={(open) => !open && setPendingDelete(null)}
            >
                <DialogContent>
                    <DialogTitle>Remover {pendingDelete?.name}?</DialogTitle>
                    <DialogDescription>
                        Essa ação não pode ser desfeita.
                    </DialogDescription>
                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <button
                                type="button"
                                className="inline-flex h-9 items-center justify-center rounded-md border px-4 text-sm font-medium hover:bg-accent"
                            >
                                Cancelar
                            </button>
                        </DialogClose>
                        <button
                            type="button"
                            onClick={confirmDelete}
                            className="inline-flex h-9 items-center justify-center rounded-md bg-destructive px-4 text-sm font-medium text-white hover:bg-destructive/90"
                        >
                            Remover
                        </button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
