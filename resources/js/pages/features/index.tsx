import { Form, Head, Link, router } from '@inertiajs/react';
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
import { usePermissions } from '@/hooks/use-permissions';
import { index as featureCategoriesIndex } from '@/routes/feature-categories';
import { create, destroy, edit, index, toggle } from '@/routes/features';
import type { BreadcrumbItem } from '@/types';

type FeatureCategoryOption = {
    id: number;
    name: string;
};

type FeatureRow = {
    id: number;
    name: string;
    active: boolean;
    feature_category: {
        id: number;
        name: string;
    };
};

type Props = {
    features: FeatureRow[];
    featureCategories: FeatureCategoryOption[];
    selectedCategoryId: number | null;
};

const ALL_CATEGORIES = 'all';

export default function FeaturesIndex({
    features,
    featureCategories,
    selectedCategoryId,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('caracteristicas.criar');
    const canEdit = can('caracteristicas.editar');
    const canDelete = can('caracteristicas.excluir');

    const handleCategoryFilterChange = (value: string) => {
        router.get(
            index({
                query:
                    value === ALL_CATEGORIES
                        ? {}
                        : { feature_category_id: value },
            }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Características" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Características"
                        description="Catálogo de características que podem ser usadas em qualquer imóvel"
                    />
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link href={featureCategoriesIndex()}>
                                Gerenciar categorias
                            </Link>
                        </Button>
                        {canCreate && (
                            <Button asChild>
                                <Link
                                    href={
                                        selectedCategoryId
                                            ? create({
                                                  query: {
                                                      feature_category_id:
                                                          selectedCategoryId,
                                                  },
                                              })
                                            : create()
                                    }
                                >
                                    <Plus />
                                    Nova característica
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Select
                        value={selectedCategoryId?.toString() ?? ALL_CATEGORIES}
                        onValueChange={handleCategoryFilterChange}
                    >
                        <SelectTrigger className="w-64">
                            <SelectValue placeholder="Filtrar por categoria" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL_CATEGORIES}>
                                Todas as categorias
                            </SelectItem>
                            {featureCategories.map((category) => (
                                <SelectItem
                                    key={category.id}
                                    value={category.id.toString()}
                                >
                                    {category.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Categoria</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {features.map((feature) => (
                            <TableRow key={feature.id}>
                                <TableCell className="font-medium">
                                    {feature.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {feature.feature_category.name}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant={
                                            feature.active
                                                ? 'default'
                                                : 'outline'
                                        }
                                    >
                                        {feature.active ? 'Ativa' : 'Inativa'}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Form action={toggle(feature.id)}>
                                                {({ processing }) => (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        {feature.active ? (
                                                            <PowerOff />
                                                        ) : (
                                                            <Power />
                                                        )}
                                                        <span className="sr-only">
                                                            {feature.active
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
                                                <Link href={edit(feature.id)}>
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
                                                        Excluir {feature.name}?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            feature.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.feature
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
        title: 'Características',
        href: index(),
    },
];

FeaturesIndex.layout = {
    breadcrumbs,
};
