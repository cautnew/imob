import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/price-types';
import type { BreadcrumbItem } from '@/types';

type EditablePriceType = {
    id: number;
    name: string;
    comparable: boolean;
};

type Props = {
    priceType: EditablePriceType;
};

export default function PriceTypesEdit({ priceType }: Props) {
    return (
        <>
            <Head title={`Editar ${priceType.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar tipo de preço"
                    description={`Atualize os dados de ${priceType.name}`}
                />

                <Card>
                    <CardContent>
                        <Form
                            action={update(priceType.id)}
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
                                            defaultValue={priceType.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <label className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            name="comparable"
                                            value="1"
                                            defaultChecked={
                                                priceType.comparable
                                            }
                                        />
                                        Incluir na comparação entre imóveis
                                    </label>

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
        title: 'Tipos de preço',
        href: index(),
    },
    {
        title: 'Editar tipo de preço',
        href: '',
    },
];

PriceTypesEdit.layout = {
    breadcrumbs,
};
