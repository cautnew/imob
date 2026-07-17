import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, store } from '@/routes/price-types';
import type { BreadcrumbItem } from '@/types';

export default function PriceTypesCreate() {
    return (
        <>
            <Head title="Novo tipo de preço" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Novo tipo de preço"
                    description="Adicione um tipo de preço ao catálogo da sua imobiliária"
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
                                            placeholder="Ex: Venda, Aluguel, Condomínio"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <label className="flex items-center gap-2 text-sm">
                                        <Checkbox name="comparable" value="1" />
                                        Incluir na comparação entre imóveis
                                    </label>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Criar tipo de preço
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
        title: 'Tipos de preço',
        href: index(),
    },
    {
        title: 'Novo tipo de preço',
        href: '',
    },
];

PriceTypesCreate.layout = {
    breadcrumbs,
};
