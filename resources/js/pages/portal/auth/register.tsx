import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes/portal';
import { store as register } from '@/routes/portal/register';

export default function PortalRegister() {
    return (
        <>
            <Head title="Cadastro no portal" />
            <Form
                action={register()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
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
                                    required
                                    autoFocus
                                    autoComplete="username"
                                    name="document"
                                    placeholder="000.000.000-00"
                                />
                                <InputError
                                    message={errors.document}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mail</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Senha</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Senha"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar senha
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirmar senha"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <p className="text-xs text-muted-foreground">
                                Informe o CPF e o e-mail cadastrados junto à sua
                                imobiliária para vincular o acesso ao seu
                                contrato.
                            </p>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                Criar acesso
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Já tem acesso?{' '}
                            <TextLink href={login()}>Entrar</TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

PortalRegister.layout = {
    title: 'Portal do Inquilino',
    description: 'Informe seus dados para criar o acesso ao portal',
};
