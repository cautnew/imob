import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/users';
import type { BreadcrumbItem } from '@/types';

type Role = {
    id: number;
    name: string;
};

type EditableUser = {
    id: number;
    name: string;
    email: string;
    is_owner: boolean;
};

type Props = {
    user: EditableUser;
    assignedRoles: number[];
    roles: Role[];
};

export default function UsersEdit({ user, assignedRoles, roles }: Props) {
    return (
        <>
            <Head title={`Editar ${user.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar usuário"
                    description={`Atualize os dados de ${user.name}`}
                />

                <Card>
                    <CardContent>
                        <Form
                            {...update.form(user.id)}
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
                                            autoComplete="name"
                                            name="name"
                                            defaultValue={user.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="email">E-mail</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            required
                                            autoComplete="email"
                                            name="email"
                                            defaultValue={user.email}
                                        />
                                        <InputError message={errors.email} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password">
                                            Nova senha
                                        </Label>
                                        <PasswordInput
                                            id="password"
                                            autoComplete="new-password"
                                            name="password"
                                            placeholder="Deixe em branco para manter a atual"
                                        />
                                        <InputError
                                            message={errors.password}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password_confirmation">
                                            Confirmar nova senha
                                        </Label>
                                        <PasswordInput
                                            id="password_confirmation"
                                            autoComplete="new-password"
                                            name="password_confirmation"
                                        />
                                        <InputError
                                            message={
                                                errors.password_confirmation
                                            }
                                        />
                                    </div>

                                    {!user.is_owner && (
                                        <div className="grid gap-2">
                                            <Label>Papéis</Label>
                                            <div className="flex flex-col gap-2">
                                                {roles.map((role) => (
                                                    <label
                                                        key={role.id}
                                                        className="flex items-center gap-2 text-sm"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            name="roles[]"
                                                            value={role.id}
                                                            defaultChecked={assignedRoles.includes(
                                                                role.id,
                                                            )}
                                                            className="size-4 rounded border-input accent-primary"
                                                        />
                                                        {role.name}
                                                    </label>
                                                ))}
                                            </div>
                                            <InputError
                                                message={errors.roles}
                                            />
                                        </div>
                                    )}

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
        title: 'Usuários',
        href: index(),
    },
    {
        title: 'Editar usuário',
        href: '',
    },
];

UsersEdit.layout = {
    breadcrumbs,
};
