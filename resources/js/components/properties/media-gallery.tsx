import {
    DndContext,
    
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors
} from '@dnd-kit/core';
import type {DragEndEvent} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    rectSortingStrategy,
    sortableKeyboardCoordinates,
    useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { router } from '@inertiajs/react';
import {
    GripVertical,
    ImagePlus,
    MoreVertical,
    Star,
    Trash2,
    UploadCloud,
} from 'lucide-react';
import { useRef, useState } from 'react';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { cover, destroy, reorder, store, update } from '@/routes/property-media';

export type MediaItem = {
    id: number;
    url: string;
    caption: string | null;
    is_cover: boolean;
    sort_order: number;
};

type Props = {
    propertyId: number;
    media: MediaItem[];
};

export default function MediaGallery({ propertyId, media }: Props) {
    const [items, setItems] = useState<MediaItem[]>(media);
    const [syncedMedia, setSyncedMedia] = useState(media);
    const [uploading, setUploading] = useState(false);
    const [dragOver, setDragOver] = useState(false);
    const [pendingDelete, setPendingDelete] = useState<MediaItem | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    if (media !== syncedMedia) {
        setSyncedMedia(media);
        setItems(media);
    }

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 4 },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const uploadFiles = (fileList: FileList | File[]) => {
        const files = Array.from(fileList);

        if (files.length === 0) {
            return;
        }

        setUploading(true);

        router.post(
            store(propertyId).url,
            { files },
            {
                forceFormData: true,
                preserveScroll: true,
                only: ['media'],
                onFinish: () => setUploading(false),
            },
        );
    };

    const handleDrop = (event: React.DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        setDragOver(false);
        uploadFiles(event.dataTransfer.files);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        const oldIndex = items.findIndex((item) => item.id === active.id);
        const newIndex = items.findIndex((item) => item.id === over.id);
        const reordered = arrayMove(items, oldIndex, newIndex);

        setItems(reordered);

        router.post(
            reorder(propertyId).url,
            { order: reordered.map((item) => item.id) },
            { preserveScroll: true, preserveState: true, only: ['media'] },
        );
    };

    const setCaption = (mediaId: number, caption: string) => {
        setItems((current) =>
            current.map((item) =>
                item.id === mediaId ? { ...item, caption } : item,
            ),
        );
    };

    const saveCaption = (mediaId: number, caption: string) => {
        router.patch(
            update([propertyId, mediaId]).url,
            { caption },
            { preserveScroll: true, preserveState: true, only: ['media'] },
        );
    };

    const setAsCover = (mediaId: number) => {
        router.post(
            cover([propertyId, mediaId]).url,
            {},
            { preserveScroll: true, preserveState: true, only: ['media'] },
        );
    };

    const confirmDelete = () => {
        if (!pendingDelete) {
            return;
        }

        router.delete(destroy([propertyId, pendingDelete.id]).url, {
            preserveScroll: true,
            only: ['media'],
            onSuccess: () => setPendingDelete(null),
        });
    };

    return (
        <div className="flex flex-col gap-6">
            <div
                onDragOver={(event) => {
                    event.preventDefault();
                    setDragOver(true);
                }}
                onDragLeave={() => setDragOver(false)}
                onDrop={handleDrop}
                onClick={() => fileInputRef.current?.click()}
                role="button"
                tabIndex={0}
                className={cn(
                    'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-10 text-center transition-colors',
                    dragOver
                        ? 'border-primary bg-primary/5'
                        : 'border-muted-foreground/25 hover:border-muted-foreground/50',
                )}
            >
                {uploading ? (
                    <Spinner className="size-8" />
                ) : (
                    <UploadCloud className="size-8 text-muted-foreground" />
                )}
                <p className="text-sm font-medium">
                    Arraste fotos aqui ou clique para selecionar
                </p>
                <p className="text-xs text-muted-foreground">
                    JPG, PNG ou WEBP — até 10MB por foto
                </p>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    className="hidden"
                    onChange={(event) => {
                        if (event.target.files) {
                            uploadFiles(event.target.files);
                            event.target.value = '';
                        }
                    }}
                />
            </div>

            {items.length === 0 ? (
                <div className="flex flex-col items-center gap-2 rounded-lg border border-dashed p-10 text-center text-muted-foreground">
                    <ImagePlus className="size-8" />
                    <p className="text-sm">Nenhuma foto cadastrada ainda.</p>
                </div>
            ) : (
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragEnd={handleDragEnd}
                >
                    <SortableContext
                        items={items.map((item) => item.id)}
                        strategy={rectSortingStrategy}
                    >
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {items.map((item) => (
                                <MediaCard
                                    key={item.id}
                                    item={item}
                                    onCaptionChange={(value) =>
                                        setCaption(item.id, value)
                                    }
                                    onCaptionSave={(value) =>
                                        saveCaption(item.id, value)
                                    }
                                    onSetCover={() => setAsCover(item.id)}
                                    onDelete={() => setPendingDelete(item)}
                                />
                            ))}
                        </div>
                    </SortableContext>
                </DndContext>
            )}

            <Dialog
                open={pendingDelete !== null}
                onOpenChange={(open) => !open && setPendingDelete(null)}
            >
                <DialogContent>
                    <DialogTitle>Remover foto?</DialogTitle>
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

type MediaCardProps = {
    item: MediaItem;
    onCaptionChange: (value: string) => void;
    onCaptionSave: (value: string) => void;
    onSetCover: () => void;
    onDelete: () => void;
};

function MediaCard({
    item,
    onCaptionChange,
    onCaptionSave,
    onSetCover,
    onDelete,
}: MediaCardProps) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } =
        useSortable({ id: item.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={cn(
                'group relative flex flex-col gap-2 rounded-lg border bg-card p-2 shadow-sm',
                isDragging && 'z-10 opacity-70',
            )}
        >
            <div className="relative aspect-video overflow-hidden rounded-md bg-muted">
                <img
                    src={item.url}
                    alt={item.caption ?? 'Foto do imóvel'}
                    className="size-full object-cover"
                />

                {item.is_cover && (
                    <span className="absolute top-2 left-2 inline-flex items-center gap-1 rounded-md bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground">
                        <Star className="size-3 fill-current" />
                        Capa
                    </span>
                )}

                <button
                    type="button"
                    {...attributes}
                    {...listeners}
                    className="absolute top-2 right-2 flex size-7 cursor-grab items-center justify-center rounded-md bg-background/80 text-foreground opacity-0 backdrop-blur transition-opacity group-hover:opacity-100 active:cursor-grabbing"
                >
                    <GripVertical className="size-4" />
                    <span className="sr-only">Arrastar para reordenar</span>
                </button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            className="absolute bottom-2 right-2 flex size-7 items-center justify-center rounded-md bg-background/80 text-foreground opacity-0 backdrop-blur transition-opacity group-hover:opacity-100"
                        >
                            <MoreVertical className="size-4" />
                            <span className="sr-only">Mais ações</span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {!item.is_cover && (
                            <DropdownMenuItem onClick={onSetCover}>
                                <Star />
                                Definir como capa
                            </DropdownMenuItem>
                        )}
                        <DropdownMenuItem variant="destructive" onClick={onDelete}>
                            <Trash2 />
                            Remover
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <Input
                placeholder="Adicionar legenda"
                value={item.caption ?? ''}
                onChange={(event) => onCaptionChange(event.target.value)}
                onBlur={(event) => onCaptionSave(event.target.value)}
                className="h-8 text-sm"
            />
        </div>
    );
}
