import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/roles';
import type { BreadcrumbItem } from '@/types';

type Permission = {
    id: number;
    name: string;
};

type EditableRole = {
    id: number;
    name: string;
};

type Props = {
    role: EditableRole;
    assignedPermissions: number[];
    permissions: Permission[];
};

const GROUP_LABELS: Record<string, string> = {
    usuarios: 'Usuários',
    papeis: 'Papéis',
    permissoes: 'Permissões',
};

function groupPermissions(permissions: Permission[]) {
    const groups = new Map<string, Permission[]>();

    for (const permission of permissions) {
        const [prefix] = permission.name.split('.');
        const group = groups.get(prefix) ?? [];
        group.push(permission);
        groups.set(prefix, group);
    }

    return groups;
}

export default function RolesEdit({
    role,
    assignedPermissions,
    permissions,
}: Props) {
    const groups = groupPermissions(permissions);

    return (
        <>
            <Head title={`Editar ${role.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar papel"
                    description={`Atualize as permissões de ${role.name}`}
                />

                <Card>
                    <CardContent>
                        <Form
                            {...update.form(role.id)}
                            disableWhileProcessing
                            className="flex flex-col gap-6"
                        >
                            {({ processing, errors }) => (
                                <div className="grid gap-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Nome</Label>
                                        <Input
                                            id="name"
                                            type="text"
                                            required
                                            name="name"
                                            defaultValue={role.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-4">
                                        <Label>Permissões</Label>
                                        {[...groups.entries()].map(
                                            ([prefix, items]) => (
                                                <Card key={prefix}>
                                                    <CardHeader>
                                                        <CardTitle className="text-sm">
                                                            {GROUP_LABELS[
                                                                prefix
                                                            ] ?? prefix}
                                                        </CardTitle>
                                                    </CardHeader>
                                                    <CardContent className="flex flex-col gap-2">
                                                        {items.map(
                                                            (permission) => (
                                                                <label
                                                                    key={
                                                                        permission.id
                                                                    }
                                                                    className="flex items-center gap-2 text-sm"
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        name="permissions[]"
                                                                        value={
                                                                            permission.id
                                                                        }
                                                                        defaultChecked={assignedPermissions.includes(
                                                                            permission.id,
                                                                        )}
                                                                        className="size-4 rounded border-input accent-primary"
                                                                    />
                                                                    {
                                                                        permission.name
                                                                    }
                                                                </label>
                                                            ),
                                                        )}
                                                    </CardContent>
                                                </Card>
                                            ),
                                        )}
                                        <InputError
                                            message={errors.permissions}
                                        />
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Salvar alterações
                                        </Button>
                                        <Button variant="outline" asChild>
                                            <a href={index().url}>Cancelar</a>
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Papéis',
        href: index(),
    },
    {
        title: 'Editar papel',
        href: '',
    },
];

RolesEdit.layout = {
    breadcrumbs,
};
