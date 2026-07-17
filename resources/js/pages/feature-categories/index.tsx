import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, Plus, Power, PowerOff, Trash2 } from 'lucide-react';
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
import { create, destroy, edit, index, toggle } from '@/routes/feature-categories';
import { index as featuresIndex } from '@/routes/features';
import type { BreadcrumbItem } from '@/types';

type FeatureCategoryRow = {
    id: number;
    name: string;
    active: boolean;
    features_count: number;
};

type Props = {
    featureCategories: FeatureCategoryRow[];
};

export default function FeatureCategoriesIndex({ featureCategories }: Props) {
    const { can } = usePermissions();

    const canCreate = can('caracteristicas.criar');
    const canEdit = can('caracteristicas.editar');
    const canDelete = can('caracteristicas.excluir');

    return (
        <>
            <Head title="Categorias de características" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Categorias de características"
                        description="Organize o catálogo de características dos imóveis"
                    />
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link href={featuresIndex()}>
                                Ver características
                            </Link>
                        </Button>
                        {canCreate && (
                            <Button asChild>
                                <Link href={create()}>
                                    <Plus />
                                    Nova categoria
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Características</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {featureCategories.map((category) => (
                            <TableRow key={category.id}>
                                <TableCell className="font-medium">
                                    <Link
                                        href={
                                            featuresIndex({
                                                query: {
                                                    feature_category_id:
                                                        category.id,
                                                },
                                            }).url
                                        }
                                        className="underline-offset-4 hover:underline"
                                    >
                                        {category.name}
                                    </Link>
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {category.features_count}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant={
                                            category.active
                                                ? 'default'
                                                : 'outline'
                                        }
                                    >
                                        {category.active ? 'Ativa' : 'Inativa'}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Form
                                                action={toggle(category.id)}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {category.active ? (
                                                            <PowerOff />
                                                        ) : (
                                                            <Power />
                                                        )}
                                                        <span className="sr-only">
                                                            {category.active
                                                                ? 'Desativar'
                                                                : 'Ativar'}
                                                        </span>
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                        {canEdit && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={edit(category.id)}
                                                >
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
                                                        desfeita. Categorias com
                                                        características
                                                        vinculadas não podem
                                                        ser excluídas.
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
                                                                        errors.feature_category
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
        title: 'Categorias de características',
        href: index(),
    },
];

FeatureCategoriesIndex.layout = {
    breadcrumbs,
};
