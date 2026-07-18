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
import { create, destroy, edit, index } from '@/routes/lessees';
import type { BreadcrumbItem } from '@/types';

type LesseeRow = {
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
    lessees: Paginated<LesseeRow>;
    filters: {
        state: string | null;
        search: string | null;
    };
};

export default function LesseesIndex({ lessees, filters }: Props) {
    const { can } = usePermissions();

    const canCreate = can('inquilinos.criar');
    const canEdit = can('inquilinos.editar');
    const canDelete = can('inquilinos.excluir');

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
            <Head title="Inquilinos" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Inquilinos"
                        description="Gerencie os inquilinos cadastrados na sua imobiliária"
                    />
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Novo inquilino
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
                        {lessees.data.map((lessee) => (
                            <TableRow key={lessee.id}>
                                <TableCell className="font-medium">
                                    {lessee.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {lessee.document}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {lessee.phone}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {lessee.city}/{lessee.state}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {lessee.properties_count > 0
                                        ? `${lessee.properties_count} imóve${lessee.properties_count > 1 ? 'is' : 'l'}`
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
                                                <Link href={edit(lessee.id)}>
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
                                                        Excluir {lessee.name}?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Essa ação não pode ser
                                                        desfeita.
                                                    </DialogDescription>
                                                    <Form
                                                        action={destroy(
                                                            lessee.id,
                                                        )}
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <InputError
                                                                    message={
                                                                        errors.lessee
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

                {lessees.last_page > 1 && (
                    <div className="flex flex-wrap items-center gap-1">
                        {lessees.links.map((link, index) => (
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
        title: 'Inquilinos',
        href: index(),
    },
];

LesseesIndex.layout = {
    breadcrumbs,
};
