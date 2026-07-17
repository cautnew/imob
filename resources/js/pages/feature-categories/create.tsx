import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, store } from '@/routes/feature-categories';
import type { BreadcrumbItem } from '@/types';

export default function FeatureCategoriesCreate() {
    return (
        <>
            <Head title="Nova categoria" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Nova categoria"
                    description="Crie uma nova categoria para organizar características"
                />

                <Card>
                    <CardContent>
                        <Form
                            action={store()}
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
                                            placeholder="Ex: Área de lazer"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Criar categoria
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
        title: 'Categorias de características',
        href: index(),
    },
    {
        title: 'Nova categoria',
        href: '',
    },
];

FeatureCategoriesCreate.layout = {
    breadcrumbs,
};
