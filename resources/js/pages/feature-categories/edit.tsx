import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/feature-categories';
import type { BreadcrumbItem } from '@/types';

type EditableFeatureCategory = {
    id: number;
    name: string;
    active: boolean;
};

type Props = {
    featureCategory: EditableFeatureCategory;
};

export default function FeatureCategoriesEdit({ featureCategory }: Props) {
    return (
        <>
            <Head title={`Editar ${featureCategory.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar categoria"
                    description={`Atualize os dados de ${featureCategory.name}`}
                />

                <Card>
                    <CardContent>
                        <Form
                            action={update(featureCategory.id)}
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
                                            defaultValue={featureCategory.name}
                                        />
                                        <InputError message={errors.name} />
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
        title: 'Categorias de características',
        href: index(),
    },
    {
        title: 'Editar categoria',
        href: '',
    },
];

FeatureCategoriesEdit.layout = {
    breadcrumbs,
};
