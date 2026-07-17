import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/permissions';
import type { BreadcrumbItem } from '@/types';

type Permission = {
    id: number;
    name: string;
};

type Props = {
    permissions: Permission[];
};

export default function PermissionsIndex({ permissions }: Props) {
    return (
        <>
            <Head title="Permissões" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Permissões"
                    description="Catálogo de permissões disponíveis no sistema, usado para montar os papéis"
                />

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Permissão</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {permissions.map((permission) => (
                            <TableRow key={permission.id}>
                                <TableCell className="font-mono text-sm">
                                    {permission.name}
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
        title: 'Permissões',
        href: index(),
    },
];

PermissionsIndex.layout = {
    breadcrumbs,
};
