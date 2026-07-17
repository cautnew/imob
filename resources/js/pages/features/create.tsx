import { Form, Head } from '@inertiajs/react';
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
import { index, store } from '@/routes/features';
import type { BreadcrumbItem } from '@/types';

type FeatureCategory = {
    id: number;
    name: string;
};

type Props = {
    featureCategories: FeatureCategory[];
    selectedCategoryId: number | null;
};

export default function FeaturesCreate({
    featureCategories,
    selectedCategoryId,
}: Props) {
    const backHref = selectedCategoryId
        ? index({ query: { feature_category_id: selectedCategoryId } }).url
        : index().url;

    return (
        <>
            <Head title="Nova característica" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Nova característica"
                    description="Adicione uma característica ao catálogo da sua imobiliária"
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
                                            placeholder="Ex: Piscina"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="feature_category_id">
                                            Categoria
                                        </Label>
                                        <Select
                                            name="feature_category_id"
                                            required
                                            defaultValue={selectedCategoryId?.toString()}
                                        >
                                            <SelectTrigger
                                                id="feature_category_id"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Selecione uma categoria" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {featureCategories.map(
                                                    (category) => (
                                                        <SelectItem
                                                            key={category.id}
                                                            value={category.id.toString()}
                                                        >
                                                            {category.name}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={
                                                errors.feature_category_id
                                            }
                                        />
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Criar característica
                                        </Button>
                                        <Button variant="outline" asChild>
                                            <a href={backHref}>Cancelar</a>
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
        title: 'Características',
        href: index(),
    },
    {
        title: 'Nova característica',
        href: '',
    },
];

FeaturesCreate.layout = {
    breadcrumbs,
};
