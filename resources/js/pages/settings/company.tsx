import { Form, Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { edit, update } from '@/routes/company';
import { home as showPublicHome } from '@/routes/public';
import type { Auth } from '@/types';

type Company = {
    id: number;
    name: string;
    slug: string;
};

type PageProps = {
    auth: Auth;
    company: Company;
};

export default function CompanySettings() {
    const { company } = usePage<PageProps>().props;
    const [slug, setSlug] = useState(company.slug);

    return (
        <>
            <Head title="Portal público" />

            <h1 className="sr-only">Portal público</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Portal público"
                    description="Defina o endereço público dos imóveis da sua imobiliária"
                />

                <Form
                    action={update()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="slug">
                                    Endereço da imobiliária
                                </Label>

                                <Input
                                    id="slug"
                                    className="mt-1 block w-full"
                                    value={slug}
                                    name="slug"
                                    required
                                    onChange={(e) => setSlug(e.target.value)}
                                    placeholder="minha-imobiliaria"
                                />

                                <p className="text-sm text-muted-foreground">
                                    {slug
                                        ? showPublicHome.url({
                                              companySlug: slug,
                                          })
                                        : 'Somente letras minúsculas, números e hífens.'}
                                </p>

                                <InputError
                                    className="mt-2"
                                    message={errors.slug}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    {processing && <Spinner />}
                                    Salvar
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CompanySettings.layout = {
    breadcrumbs: [
        {
            title: 'Portal público',
            href: edit(),
        },
    ],
};
