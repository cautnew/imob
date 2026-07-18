import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/price-types';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type EditablePriceType = {
    id: number;
    name: string;
    purpose: string | null;
    comparable: boolean;
};

type Props = {
    priceType: EditablePriceType;
    purposes: Option[];
};

export default function PriceTypesEdit({ priceType, purposes }: Props) {
    const [purpose, setPurpose] = useState(priceType.purpose ?? '');

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

                                    <div className="grid gap-2">
                                        <Label htmlFor="purpose">
                                            Finalidade
                                        </Label>
                                        <Select
                                            name="purpose"
                                            value={purpose}
                                            onValueChange={setPurpose}
                                        >
                                            <SelectTrigger
                                                id="purpose"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Nenhuma (não usado no filtro de preço do portal público)" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {purposes.map((option) => (
                                                    <SelectItem
                                                        key={option.value}
                                                        value={option.value}
                                                    >
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.purpose} />
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
