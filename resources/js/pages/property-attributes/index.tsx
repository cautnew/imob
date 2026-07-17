import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/hooks/use-permissions';
import { create, destroy, edit, index } from '@/routes/property-attributes';
import type { BreadcrumbItem } from '@/types';

type TypeOption = {
    value: string;
    label: string;
};

type PropertyAttributeRow = {
    id: number;
    name: string;
    type: string;
    filterable: boolean;
    comparable: boolean;
    required: boolean;
    options_count: number;
};

type Props = {
    propertyAttributes: PropertyAttributeRow[];
    types: TypeOption[];
};

export default function PropertyAttributesIndex({
    propertyAttributes,
    types,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('atributos.criar');
    const canEdit = can('atributos.editar');
    const canDelete = can('atributos.excluir');

    const typeLabel = (type: string) =>
        types.find((option) => option.value === type)?.label ?? type;

    return (
        <>
            <Head title="Atributos" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Atributos"
                        description="Catálogo de atributos dinâmicos que podem ser usados em qualquer imóvel"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo atributo
                            </Link>
                        </Button>
                    )}
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Opções</TableHead>
                            <TableHead>Flags</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {propertyAttributes.map((attribute) => (
                            <TableRow key={attribute.id}>
                                <TableCell className="font-medium">
                                    {attribute.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {typeLabel(attribute.type)}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {attribute.options_count > 0
                                        ? attribute.options_count
                                        : '—'}
                                </TableCell>
                                <TableCell>
                                    <div className="flex flex-wrap gap-1">
                                        {attribute.filterable && (
                                            <Badge variant="outline">
                                                Filtrável
                                            </Badge>
                                        )}
                                        {attribute.comparable && (
                                            <Badge variant="outline">
                                                Comparável
                                            </Badge>
                                        )}
                                        {attribute.required && (
                                            <Badge variant="outline">
                                                Obrigatório
                                            </Badge>
                                        )}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link href={edit(attribute.id)}>
                                                    <Pencil />
                                                    <span className="sr-only">
                                                        Editar
                                                    </span>
                                                </Link>
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                    >
                                                        <Trash2 />
                                                        <span className="sr-only">
                                                            Excluir
                                                        </span>
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogTitle>
                                                        Excluir {attribute.name}
                                                        ?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            attribute.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.property_attribute
                                                                    }
                                                                />
                                                                <DialogFooter className="gap-2">
                                                                    <DialogClose
                                                                        asChild
                                                                    >
                                                                        <Button variant="secondary">
                                                                            Cancelar
                                                                        </Button>
                                                                    </DialogClose>
                                                                    <Button
                                                                        variant="destructive"
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        Excluir
                                                                    </Button>
                                                                </DialogFooter>
                                                            </>
                                                        )}
                                                    </Form>
                                                </DialogContent>
                                            </Dialog>
                                        )}
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atributos',
        href: index(),
    },
];

PropertyAttributesIndex.layout = {
    breadcrumbs,
};
