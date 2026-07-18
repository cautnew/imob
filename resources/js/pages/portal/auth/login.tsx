import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes/portal';
import { store as login } from '@/routes/portal/login';
import { request } from '@/routes/portal/password';

export default function PortalLogin() {
    return (
        <>
            <Head title="Entrar no portal" />

            <Form
                action={login()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="document">CPF</Label>
                                <Input
                                    id="document"
                                    type="text"
                                    name="document"
                                    required
                                    autoFocus
                                    autoComplete="username"
                                    placeholder="000.000.000-00"
                                />
                                <InputError message={errors.document} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">Senha</Label>
                                    <TextLink
                                        href={request()}
                                        className="ml-auto text-sm"
                                    >
                                        Esqueceu a senha?
                                    </TextLink>
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    autoComplete="current-password"
                                    placeholder="Senha"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox id="remember" name="remember" />
                                <Label htmlFor="remember">Lembrar de mim</Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                Entrar
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Ainda não tem acesso ao portal?{' '}
                            <TextLink href={register()}>Cadastre-se</TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

PortalLogin.layout = {
    title: 'Portal do Inquilino',
    description: 'Informe seu CPF e senha para acessar o portal',
};
