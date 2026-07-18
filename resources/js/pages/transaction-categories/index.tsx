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
import { create, destroy, edit, index } from '@/routes/transaction-categories';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type TransactionCategoryRow = {
    id: number;
    name: string;
    type: string;
    transactions_count: number;
};

type Props = {
    transactionCategories: TransactionCategoryRow[];
    types: Option[];
};

export default function TransactionCategoriesIndex({
    transactionCategories,
    types,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('financeiro.criar');
    const canEdit = can('financeiro.editar');
    const canDelete = can('financeiro.excluir');

    const typeLabel = (type: string) =>
        types.find((option) => option.value === type)?.label ?? type;

    return (
        <>
            <Head title="Categorias financeiras" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Categorias financeiras"
                        description="Catálogo de categorias de receita e despesa usadas nos lançamentos"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Nova categoria
                            </Link>
                        </Button>
                    )}
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Lançamentos</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {transactionCategories.map((category) => (
                            <TableRow key={category.id}>
                                <TableCell className="font-medium">
                                    {category.name}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant={
                                            category.type === 'receita'
                                                ? 'default'
                                                : 'outline'
                                        }
                                    >
                                        {typeLabel(category.type)}
                                    </Badge>
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {category.transactions_count}
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link href={edit(category.id)}>
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
                                                        Excluir {category.name}?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita. Categorias
                                                        vinculadas a lançamentos
                                                        não podem ser excluídas.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            category.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.transaction_category
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
        title: 'Categorias financeiras',
        href: index(),
    },
];

TransactionCategoriesIndex.layout = {
    breadcrumbs,
};
