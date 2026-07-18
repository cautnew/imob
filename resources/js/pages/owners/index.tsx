import { Form, Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/hooks/use-permissions';
import { create, destroy, edit, index } from '@/routes/owners';
import type { BreadcrumbItem } from '@/types';

type OwnerRow = {
    id: number;
    name: string;
    document: string;
    phone: string;
    city: string;
    state: string;
    properties_count: number;
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
    owners: Paginated<OwnerRow>;
    filters: {
        state: string | null;
        search: string | null;
    };
};

export default function OwnersIndex({ owners, filters }: Props) {
    const { can } = usePermissions();

    const canCreate = can('proprietarios.criar');
    const canEdit = can('proprietarios.editar');
    const canDelete = can('proprietarios.excluir');

    const updateFilters = (next: Partial<Props['filters']>) => {
        router.get(
            index({
                query: {
                    ...(filters.state ? { state: filters.state } : {}),
                    ...(filters.search ? { search: filters.search } : {}),
                    ...next,
                },
            }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const handleFiltersSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const search = data.get('search')?.toString();
        const state = data.get('state')?.toString();
        updateFilters({
            search: search || undefined,
            state: state || undefined,
        });
    };

    return (
        <>
            <Head title="Proprietários" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Proprietários"
                        description="Gerencie os proprietários cadastrados na sua imobiliária"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo proprietário
                            </Link>
                        </Button>
                    )}
                </div>

                <form
                    onSubmit={handleFiltersSubmit}
                    className="flex flex-wrap items-center gap-2"
                >
                    <Input
                        type="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por nome ou CPF/CNPJ"
                        className="w-64"
                    />
                    <Input
                        type="text"
                        name="state"
                        maxLength={2}
                        defaultValue={filters.state ?? ''}
                        placeholder="UF"
                        className="w-20 uppercase"
                    />
                    <Button type="submit" variant="outline">
                        Buscar
                    </Button>
                </form>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>CPF/CNPJ</TableHead>
                            <TableHead>Telefone</TableHead>
                            <TableHead>Cidade/UF</TableHead>
                            <TableHead>Imóveis</TableHead>
                            <TableHead className="w-0">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {owners.data.map((owner) => (
                            <TableRow key={owner.id}>
                                <TableCell className="font-medium">
                                    {owner.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {owner.document}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {owner.phone}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {owner.city}/{owner.state}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {owner.properties_count > 0
                                        ? `${owner.properties_count} imóve${owner.properties_count > 1 ? 'is' : 'l'}`
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
                                                <Link href={edit(owner.id)}>
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
                                                        Excluir {owner.name}?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            owner.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.owner
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

                {owners.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {owners.links.map((link, index) => (
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
        title: 'Proprietários',
        href: index(),
    },
];

OwnersIndex.layout = {
    breadcrumbs,
};
