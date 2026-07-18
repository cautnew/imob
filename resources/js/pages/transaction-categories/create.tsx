import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { index, store } from '@/routes/transaction-categories';
import type { BreadcrumbItem } from '@/types';

type Option = {
    value: string;
    label: string;
};

type Props = {
    types: Option[];
};

export default function TransactionCategoriesCreate({ types }: Props) {
    const [type, setType] = useState('');

    return (
        <>
            <Head title="Nova categoria financeira" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Nova categoria financeira"
                    description="Adicione uma categoria de receita ou despesa ao catálogo da sua imobiliária"
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
                                            placeholder="Ex: IPTU, Condomínio, Aluguel"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2 sm:w-1/3">
                                        <Label htmlFor="type">Tipo</Label>
                                        <Select
                                            name="type"
                                            value={type}
                                            onValueChange={setType}
                                        >
                                            <SelectTrigger
                                                id="type"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Selecione" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {types.map((option) => (
                                                    <SelectItem
                                                        key={option.value}
                                                        value={option.value}
                                                    >
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.type} />
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
        title: 'Categorias financeiras',
        href: index(),
    },
    {
        title: 'Nova categoria',
        href: '',
    },
];

TransactionCategoriesCreate.layout = {
    breadcrumbs,
};
