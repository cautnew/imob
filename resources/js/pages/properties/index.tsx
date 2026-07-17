import { Form, Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
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
import { Input } from '@/components/ui/input';
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
import { create, destroy, edit, index } from '@/routes/properties';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type PropertyRow = {
    id: number;
    title: string;
    purpose: string;
    status: string;
    city: string;
    state: string;
    prices_count: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
};

type Props = {
    properties: Paginated<PropertyRow>;
    filters: {
        status: string | null;
        purpose: string | null;
        search: string | null;
    };
    statuses: Option[];
    purposes: Option[];
};

const ALL = 'all';

export default function PropertiesIndex({
    properties,
    filters,
    statuses,
    purposes,
}: Props) {
    const { can } = usePermissions();

    const canCreate = can('imoveis.criar');
    const canEdit = can('imoveis.editar');
    const canDelete = can('imoveis.excluir');

    const statusLabel = (status: string) =>
        statuses.find((option) => option.value === status)?.label ?? status;

    const purposeLabel = (purpose: string) =>
        purposes.find((option) => option.value === purpose)?.label ?? purpose;

    const updateFilters = (next: Partial<Props['filters']>) => {
        router.get(
            index({
                query: {
                    ...(filters.status ? { status: filters.status } : {}),
                    ...(filters.purpose ? { purpose: filters.purpose } : {}),
                    ...(filters.search ? { search: filters.search } : {}),
                    ...next,
                },
            }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const handleSearchSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const search = new FormData(event.currentTarget)
            .get('search')
            ?.toString();
        updateFilters({ search: search || undefined });
    };

    return (
        <>
            <Head title="Imóveis" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Imóveis"
                        description="Gerencie os imóveis cadastrados na sua imobiliária"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo imóvel
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <form
                        onSubmit={handleSearchSubmit}
                        className="flex items-center gap-2"
                    >
                        <Input
                            type="search"
                            name="search"
                            defaultValue={filters.search ?? ''}
                            placeholder="Buscar por título"
                            className="w-64"
                        />
                        <Button type="submit" variant="outline">
                            Buscar
                        </Button>
                    </form>

                    <Select
                        value={filters.status ?? ALL}
                        onValueChange={(value) =>
                            updateFilters({
                                status: value === ALL ? undefined : value,
                            })
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>Todos status</SelectItem>
                            {statuses.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={filters.purpose ?? ALL}
                        onValueChange={(value) =>
                            updateFilters({
                                purpose: value === ALL ? undefined : value,
                            })
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Finalidade" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                Todas finalidades
                            </SelectItem>
                            {purposes.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Título</TableHead>
                            <TableHead>Cidade/UF</TableHead>
                            <TableHead>Finalidade</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Preços</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {properties.data.map((property) => (
                            <TableRow key={property.id}>
                                <TableCell className="font-medium">
                                    {property.title}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {property.city}/{property.state}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {purposeLabel(property.purpose)}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {statusLabel(property.status)}
                                    </Badge>
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {property.prices_count > 0
                                        ? `${property.prices_count} preço${property.prices_count > 1 ? 's' : ''}`
                                        : '—'}
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        {canEdit && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link href={edit(property.id)}>
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
                                                        Excluir {property.title}
                                                        ?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            property.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.property
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

                {properties.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {properties.links.map((link, index) => (
                            <Button
                                key={`${link.label}-${index}`}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                asChild={!!link.url}
                            >
                                {link.url ? (
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Imóveis',
        href: index(),
    },
];

PropertiesIndex.layout = {
    breadcrumbs,
};
