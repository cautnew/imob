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
import { index, update } from '@/routes/features';
import type { BreadcrumbItem } from '@/types';

type FeatureCategory = {
    id: number;
    name: string;
};

type EditableFeature = {
    id: number;
    name: string;
    active: boolean;
    feature_category_id: number;
};

type Props = {
    feature: EditableFeature;
    featureCategories: FeatureCategory[];
};

export default function FeaturesEdit({ feature, featureCategories }: Props) {
    return (
        <>
            <Head title={`Editar ${feature.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar característica"
                    description={`Atualize os dados de ${feature.name}`}
                />

                <Card>
                    <CardContent>
                        <Form
                            action={update(feature.id)}
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
                                            defaultValue={feature.name}
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
                                            defaultValue={feature.feature_category_id.toString()}
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
        title: 'Características',
        href: index(),
    },
    {
        title: 'Editar característica',
        href: '',
    },
];

FeaturesEdit.layout = {
    breadcrumbs,
};
