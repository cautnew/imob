import { Form, usePage } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Textarea } from '@/components/ui/textarea';
import { show as showPublicProperty } from '@/routes/public/properties';
import type { Auth } from '@/types/auth';
import type { RouteDefinition } from '@/wayfinder';

type Option = {
    value: string;
    label: string;
};

type Feature = {
    id: number;
    name: string;
};

type FeatureCategory = {
    id: number;
    name: string;
    features: Feature[];
};

type PropertyAttributeOption = {
    id: number;
    value: string;
};

type PropertyAttribute = {
    id: number;
    name: string;
    type: string;
    required: boolean;
    options: PropertyAttributeOption[];
};

type PriceType = {
    id: number;
    name: string;
};

type PropertyPriceValue = {
    price_type_id: number;
    amount: string;
    frequency: string;
};

export type PropertyFormValues = {
    title: string;
    slug: string;
    description: string;
    purpose: string;
    type: string;
    status: string;
    zip_code: string;
    street: string;
    number: string;
    complement: string;
    neighborhood: string;
    city: string;
    state: string;
    latitude: string;
    longitude: string;
    total_area: string;
    built_area: string;
    is_public: boolean;
    feature_ids: number[];
    attribute_values: Record<number, string | number | (string | number)[]>;
    prices: PropertyPriceValue[];
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    purposes: Option[];
    types: Option[];
    statuses: Option[];
    featureCategories: FeatureCategory[];
    propertyAttributes: PropertyAttribute[];
    priceTypes: PriceType[];
    frequencies: Option[];
    defaultValues?: Partial<PropertyFormValues>;
    submitLabel: string;
    backHref: string;
};

type PriceRow = {
    price_type_id: string;
    amount: string;
    frequency: string;
};

const emptyPriceRow: PriceRow = {
    price_type_id: '',
    amount: '',
    frequency: '',
};

export default function PropertyForm({
    action,
    purposes,
    types,
    statuses,
    featureCategories,
    propertyAttributes,
    priceTypes,
    frequencies,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [slug, setSlug] = useState(defaultValues?.slug ?? '');
    const [purpose, setPurpose] = useState(defaultValues?.purpose ?? '');
    const [type, setType] = useState(defaultValues?.type ?? '');
    const [status, setStatus] = useState(
        defaultValues?.status ?? statuses[0]?.value ?? '',
    );
    const [prices, setPrices] = useState<PriceRow[]>(() =>
        defaultValues?.prices && defaultValues.prices.length > 0
            ? defaultValues.prices.map((price) => ({
                  price_type_id: String(price.price_type_id),
                  amount: price.amount,
                  frequency: price.frequency,
              }))
            : [emptyPriceRow],
    );
    const [selectAttributeValues, setSelectAttributeValues] = useState<
        Record<number, string>
    >(() => {
        const initial: Record<number, string> = {};
        propertyAttributes
            .filter((attribute) => attribute.type === 'select')
            .forEach((attribute) => {
                const value = defaultValues?.attribute_values?.[attribute.id];

                if (value !== undefined && value !== null) {
                    initial[attribute.id] = String(value);
                }
            });

        return initial;
    });

    const addPrice = () =>
        setPrices((current) => [...current, { ...emptyPriceRow }]);

    const removePrice = (index: number) =>
        setPrices((current) => current.filter((_, i) => i !== index));

    const updatePrice = (index: number, field: keyof PriceRow, value: string) =>
        setPrices((current) =>
            current.map((row, i) =>
                i === index ? { ...row, [field]: value } : row,
            ),
        );

    const isFeatureChecked = (featureId: number) =>
        (defaultValues?.feature_ids ?? []).includes(featureId);

    const multiselectDefaults = (attributeId: number): (string | number)[] => {
        const value = defaultValues?.attribute_values?.[attributeId];

        return Array.isArray(value) ? value : [];
    };

    return (
        <Form
            action={action}
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ processing, errors }) => (
                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Dados básicos</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="title">Título</Label>
                                <Input
                                    id="title"
                                    type="text"
                                    required
                                    name="title"
                                    defaultValue={defaultValues?.title}
                                    placeholder="Ex: Apartamento 3 quartos no Centro"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">Slug (URL pública)</Label>
                                <Input
                                    id="slug"
                                    type="text"
                                    name="slug"
                                    value={slug}
                                    onChange={(e) => setSlug(e.target.value)}
                                    placeholder="gerado-automaticamente-a-partir-do-titulo"
                                />
                                <p className="text-sm text-muted-foreground">
                                    {slug
                                        ? showPublicProperty.url({
                                              companySlug:
                                                  auth.user.company.slug,
                                              propertySlug: slug,
                                          })
                                        : 'Deixe em branco para gerar automaticamente a partir do título.'}
                                </p>
                                <InputError message={errors.slug} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Descrição</Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    defaultValue={defaultValues?.description}
                                    rows={4}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="purpose">Finalidade</Label>
                                    <Select
                                        name="purpose"
                                        required
                                        value={purpose}
                                        onValueChange={setPurpose}
                                    >
                                        <SelectTrigger
                                            id="purpose"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Selecione" />
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

                                <div className="grid gap-2">
                                    <Label htmlFor="type">Tipo</Label>
                                    <Select
                                        name="type"
                                        required
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

                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <Select
                                        name="status"
                                        required
                                        value={status}
                                        onValueChange={setStatus}
                                    >
                                        <SelectTrigger
                                            id="status"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Selecione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {statuses.map((option) => (
                                                <SelectItem
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.status} />
                                </div>
                            </div>

                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    name="is_public"
                                    value="1"
                                    defaultChecked={defaultValues?.is_public}
                                />
                                Publicar no portal público de imóveis
                            </label>
                            <InputError message={errors.is_public} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Endereço</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6">
                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="zip_code">CEP</Label>
                                    <Input
                                        id="zip_code"
                                        type="text"
                                        required
                                        name="zip_code"
                                        defaultValue={defaultValues?.zip_code}
                                        placeholder="00000-000"
                                    />
                                    <InputError message={errors.zip_code} />
                                </div>

                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="street">Logradouro</Label>
                                    <Input
                                        id="street"
                                        type="text"
                                        required
                                        name="street"
                                        defaultValue={defaultValues?.street}
                                    />
                                    <InputError message={errors.street} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="number">Número</Label>
                                    <Input
                                        id="number"
                                        type="text"
                                        name="number"
                                        defaultValue={defaultValues?.number}
                                    />
                                    <InputError message={errors.number} />
                                </div>

                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="complement">
                                        Complemento
                                    </Label>
                                    <Input
                                        id="complement"
                                        type="text"
                                        name="complement"
                                        defaultValue={defaultValues?.complement}
                                    />
                                    <InputError message={errors.complement} />
                                </div>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-4">
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="neighborhood">Bairro</Label>
                                    <Input
                                        id="neighborhood"
                                        type="text"
                                        required
                                        name="neighborhood"
                                        defaultValue={
                                            defaultValues?.neighborhood
                                        }
                                    />
                                    <InputError message={errors.neighborhood} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="city">Cidade</Label>
                                    <Input
                                        id="city"
                                        type="text"
                                        required
                                        name="city"
                                        defaultValue={defaultValues?.city}
                                    />
                                    <InputError message={errors.city} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="state">UF</Label>
                                    <Input
                                        id="state"
                                        type="text"
                                        required
                                        maxLength={2}
                                        name="state"
                                        className="uppercase"
                                        defaultValue={defaultValues?.state}
                                    />
                                    <InputError message={errors.state} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Geolocalização</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="latitude">Latitude</Label>
                                <Input
                                    id="latitude"
                                    type="number"
                                    step="any"
                                    name="latitude"
                                    defaultValue={defaultValues?.latitude}
                                    placeholder="-23.5505"
                                />
                                <InputError message={errors.latitude} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="longitude">Longitude</Label>
                                <Input
                                    id="longitude"
                                    type="number"
                                    step="any"
                                    name="longitude"
                                    defaultValue={defaultValues?.longitude}
                                    placeholder="-46.6333"
                                />
                                <InputError message={errors.longitude} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Preços</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            {prices.map((price, index) => (
                                <div
                                    key={index}
                                    className="grid gap-4 sm:grid-cols-[2fr_1fr_1fr_auto] sm:items-start"
                                >
                                    <div className="grid gap-1">
                                        <Label>Tipo de preço</Label>
                                        <Select
                                            name={`prices[${index}][price_type_id]`}
                                            required
                                            value={price.price_type_id}
                                            onValueChange={(value) =>
                                                updatePrice(
                                                    index,
                                                    'price_type_id',
                                                    value,
                                                )
                                            }
                                        >
                                            <SelectTrigger className="w-full">
                                                <SelectValue placeholder="Selecione" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {priceTypes.map((priceType) => (
                                                    <SelectItem
                                                        key={priceType.id}
                                                        value={String(
                                                            priceType.id,
                                                        )}
                                                    >
                                                        {priceType.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={
                                                errors[
                                                    `prices.${index}.price_type_id`
                                                ]
                                            }
                                        />
                                    </div>

                                    <div className="grid gap-1">
                                        <Label>Valor</Label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            name={`prices[${index}][amount]`}
                                            value={price.amount}
                                            onChange={(e) =>
                                                updatePrice(
                                                    index,
                                                    'amount',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={
                                                errors[`prices.${index}.amount`]
                                            }
                                        />
                                    </div>

                                    <div className="grid gap-1">
                                        <Label>Frequência</Label>
                                        <Select
                                            name={`prices[${index}][frequency]`}
                                            required
                                            value={price.frequency}
                                            onValueChange={(value) =>
                                                updatePrice(
                                                    index,
                                                    'frequency',
                                                    value,
                                                )
                                            }
                                        >
                                            <SelectTrigger className="w-full">
                                                <SelectValue placeholder="Selecione" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {frequencies.map(
                                                    (frequency) => (
                                                        <SelectItem
                                                            key={
                                                                frequency.value
                                                            }
                                                            value={
                                                                frequency.value
                                                            }
                                                        >
                                                            {frequency.label}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={
                                                errors[
                                                    `prices.${index}.frequency`
                                                ]
                                            }
                                        />
                                    </div>

                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        className="mt-6"
                                        disabled={prices.length <= 1}
                                        onClick={() => removePrice(index)}
                                    >
                                        <Trash2 />
                                        <span className="sr-only">
                                            Remover preço
                                        </span>
                                    </Button>
                                </div>
                            ))}
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="w-fit"
                                onClick={addPrice}
                            >
                                <Plus />
                                Adicionar preço
                            </Button>
                            <InputError message={errors.prices} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Áreas</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="total_area">
                                    Área total (m²)
                                </Label>
                                <Input
                                    id="total_area"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    name="total_area"
                                    defaultValue={defaultValues?.total_area}
                                />
                                <InputError message={errors.total_area} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="built_area">
                                    Área construída (m²)
                                </Label>
                                <Input
                                    id="built_area"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="built_area"
                                    defaultValue={defaultValues?.built_area}
                                />
                                <InputError message={errors.built_area} />
                            </div>
                        </CardContent>
                    </Card>

                    {featureCategories.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Características</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-6">
                                {featureCategories.map((category) => (
                                    <div
                                        key={category.id}
                                        className="grid gap-2"
                                    >
                                        <Label>{category.name}</Label>
                                        <div className="flex flex-wrap gap-x-6 gap-y-2">
                                            {category.features.map(
                                                (feature) => (
                                                    <label
                                                        key={feature.id}
                                                        className="flex items-center gap-2 text-sm"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            name="features[]"
                                                            value={feature.id}
                                                            defaultChecked={isFeatureChecked(
                                                                feature.id,
                                                            )}
                                                            className="size-4 rounded border-input accent-primary"
                                                        />
                                                        {feature.name}
                                                    </label>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                ))}
                                <InputError message={errors.features} />
                            </CardContent>
                        </Card>
                    )}

                    {propertyAttributes.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Atributos</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-6">
                                {propertyAttributes.map((attribute) => {
                                    const fieldName = `attributes[${attribute.id}]`;
                                    const defaultValue =
                                        defaultValues?.attribute_values?.[
                                            attribute.id
                                        ];

                                    return (
                                        <div
                                            key={attribute.id}
                                            className="grid gap-2"
                                        >
                                            <Label htmlFor={fieldName}>
                                                {attribute.name}
                                                {attribute.required && ' *'}
                                            </Label>

                                            {attribute.type === 'texto' && (
                                                <Input
                                                    id={fieldName}
                                                    type="text"
                                                    required={
                                                        attribute.required
                                                    }
                                                    name={fieldName}
                                                    defaultValue={
                                                        defaultValue as string
                                                    }
                                                />
                                            )}

                                            {attribute.type === 'inteiro' && (
                                                <Input
                                                    id={fieldName}
                                                    type="number"
                                                    step="1"
                                                    required={
                                                        attribute.required
                                                    }
                                                    name={fieldName}
                                                    defaultValue={
                                                        defaultValue as string
                                                    }
                                                />
                                            )}

                                            {attribute.type === 'decimal' && (
                                                <Input
                                                    id={fieldName}
                                                    type="number"
                                                    step="any"
                                                    required={
                                                        attribute.required
                                                    }
                                                    name={fieldName}
                                                    defaultValue={
                                                        defaultValue as string
                                                    }
                                                />
                                            )}

                                            {attribute.type === 'data' && (
                                                <Input
                                                    id={fieldName}
                                                    type="date"
                                                    required={
                                                        attribute.required
                                                    }
                                                    name={fieldName}
                                                    defaultValue={
                                                        defaultValue as string
                                                    }
                                                />
                                            )}

                                            {attribute.type === 'boolean' && (
                                                <label className="flex items-center gap-2 text-sm">
                                                    <Checkbox
                                                        id={fieldName}
                                                        name={fieldName}
                                                        value="1"
                                                        defaultChecked={
                                                            defaultValue ===
                                                                1 ||
                                                            defaultValue === '1'
                                                        }
                                                    />
                                                    Sim
                                                </label>
                                            )}

                                            {attribute.type === 'select' && (
                                                <Select
                                                    name={fieldName}
                                                    required={
                                                        attribute.required
                                                    }
                                                    value={
                                                        selectAttributeValues[
                                                            attribute.id
                                                        ] ?? ''
                                                    }
                                                    onValueChange={(value) =>
                                                        setSelectAttributeValues(
                                                            (current) => ({
                                                                ...current,
                                                                [attribute.id]:
                                                                    value,
                                                            }),
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger
                                                        id={fieldName}
                                                        className="w-full"
                                                    >
                                                        <SelectValue placeholder="Selecione" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {attribute.options.map(
                                                            (option) => (
                                                                <SelectItem
                                                                    key={
                                                                        option.id
                                                                    }
                                                                    value={String(
                                                                        option.id,
                                                                    )}
                                                                >
                                                                    {
                                                                        option.value
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            )}

                                            {attribute.type ===
                                                'multiselect' && (
                                                <div className="flex flex-wrap gap-x-6 gap-y-2">
                                                    {attribute.options.map(
                                                        (option) => (
                                                            <label
                                                                key={option.id}
                                                                className="flex items-center gap-2 text-sm"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    name={`${fieldName}[]`}
                                                                    value={
                                                                        option.id
                                                                    }
                                                                    defaultChecked={multiselectDefaults(
                                                                        attribute.id,
                                                                    ).includes(
                                                                        option.id,
                                                                    )}
                                                                    className="size-4 rounded border-input accent-primary"
                                                                />
                                                                {option.value}
                                                            </label>
                                                        ),
                                                    )}
                                                </div>
                                            )}

                                            <InputError
                                                message={
                                                    errors[
                                                        `attributes.${attribute.id}`
                                                    ]
                                                }
                                            />
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            {submitLabel}
                        </Button>
                        <Button variant="outline" asChild>
                            <a href={backHref}>Cancelar</a>
                        </Button>
                    </div>
                </div>
            )}
        </Form>
    );
}
