import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { index } from '@/routes/properties';
import type { BreadcrumbItem } from '@/types';

type PriceType = {
    id: number;
    name: string;
};

type FeatureRow = {
    id: number;
    name: string;
    category: string;
};

type AttributeRow = {
    id: number;
    name: string;
};

type ComparedProperty = {
    id: number;
    title: string;
    type: string;
    purpose: string;
    status: string;
    neighborhood: string;
    city: string;
    state: string;
    total_area: number;
    built_area: number | null;
    cover_url: string | null;
    principal_price: number | null;
    prices: Record<number, { amount: number; frequency: string }>;
    features: number[];
    attributes: Record<number, string>;
};

type Props = {
    properties: ComparedProperty[];
    priceTypes: PriceType[];
    features: FeatureRow[];
    attributes: AttributeRow[];
    maxProperties: number;
};

const currency = (value: number | null) =>
    value === null
        ? '—'
        : new Intl.NumberFormat('pt-BR', {
              style: 'currency',
              currency: 'BRL',
          }).format(value);

const area = (value: number | null) =>
    value === null ? '—' : `${value.toLocaleString('pt-BR')} m²`;

function ComparisonRow({ label, values }: { label: string; values: string[] }) {
    const differs = new Set(values).size > 1;

    return (
        <tr
            className={cn(
                'border-b',
                differs && 'bg-amber-50 dark:bg-amber-950/30',
            )}
        >
            <th className="w-48 bg-muted/50 p-3 text-left align-top text-sm font-medium">
                {label}
            </th>
            {values.map((value, index) => (
                <td key={index} className="p-3 align-top text-sm">
                    {value}
                </td>
            ))}
        </tr>
    );
}

function SectionRow({ label, span }: { label: string; span: number }) {
    return (
        <tr className="border-b bg-muted">
            <th
                colSpan={span}
                className="p-2 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                {label}
            </th>
        </tr>
    );
}

export default function PropertiesCompare({
    properties,
    priceTypes,
    features,
    attributes,
    maxProperties,
}: Props) {
    return (
        <>
            <Head title="Comparar imóveis" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Comparar imóveis"
                        description={`Compare até ${maxProperties} imóveis lado a lado`}
                    />
                    <Button variant="outline" asChild>
                        <Link href={index()}>
                            <ArrowLeft />
                            Voltar para imóveis
                        </Link>
                    </Button>
                </div>

                {properties.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                        Nenhum imóvel selecionado. Volte para a lista de imóveis
                        e escolha até {maxProperties} para comparar.
                    </div>
                ) : (
                    <>
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <span className="inline-block size-3 rounded-sm bg-amber-50 ring-1 ring-amber-200 dark:bg-amber-950/30 dark:ring-amber-800" />
                            Linhas destacadas indicam diferenças entre os
                            imóveis selecionados.
                        </div>

                        <div className="overflow-x-auto rounded-lg border">
                            <table className="w-full border-collapse">
                                <thead>
                                    <tr className="border-b">
                                        <th className="w-48 bg-muted/50 p-3 text-left text-sm font-medium">
                                            Imóvel
                                        </th>
                                        {properties.map((property) => (
                                            <th
                                                key={property.id}
                                                className="p-3 text-left"
                                            >
                                                <div className="flex flex-col gap-2">
                                                    {property.cover_url ? (
                                                        <img
                                                            src={
                                                                property.cover_url
                                                            }
                                                            alt={property.title}
                                                            className="h-24 w-full rounded-md object-cover"
                                                        />
                                                    ) : null}
                                                    <span className="font-semibold">
                                                        {property.title}
                                                    </span>
                                                </div>
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    <ComparisonRow
                                        label="Preço principal"
                                        values={properties.map((property) =>
                                            currency(property.principal_price),
                                        )}
                                    />
                                    {priceTypes.map((priceType) => (
                                        <ComparisonRow
                                            key={priceType.id}
                                            label={priceType.name}
                                            values={properties.map(
                                                (property) => {
                                                    const price =
                                                        property.prices[
                                                            priceType.id
                                                        ];

                                                    return price
                                                        ? `${currency(price.amount)} (${price.frequency})`
                                                        : '—';
                                                },
                                            )}
                                        />
                                    ))}
                                    <ComparisonRow
                                        label="Tipo"
                                        values={properties.map(
                                            (property) => property.type,
                                        )}
                                    />
                                    <ComparisonRow
                                        label="Finalidade"
                                        values={properties.map(
                                            (property) => property.purpose,
                                        )}
                                    />
                                    <ComparisonRow
                                        label="Status"
                                        values={properties.map(
                                            (property) => property.status,
                                        )}
                                    />
                                    <ComparisonRow
                                        label="Bairro / Cidade"
                                        values={properties.map(
                                            (property) =>
                                                `${property.neighborhood}, ${property.city}/${property.state}`,
                                        )}
                                    />
                                    <ComparisonRow
                                        label="Área total"
                                        values={properties.map((property) =>
                                            area(property.total_area),
                                        )}
                                    />
                                    <ComparisonRow
                                        label="Área construída"
                                        values={properties.map((property) =>
                                            area(property.built_area),
                                        )}
                                    />

                                    {features.length > 0 && (
                                        <SectionRow
                                            label="Características"
                                            span={properties.length + 1}
                                        />
                                    )}
                                    {features.map((feature) => (
                                        <ComparisonRow
                                            key={feature.id}
                                            label={feature.name}
                                            values={properties.map(
                                                (property) =>
                                                    property.features.includes(
                                                        feature.id,
                                                    )
                                                        ? 'Sim'
                                                        : 'Não',
                                            )}
                                        />
                                    ))}

                                    {attributes.length > 0 && (
                                        <SectionRow
                                            label="Atributos personalizados"
                                            span={properties.length + 1}
                                        />
                                    )}
                                    {attributes.map((attribute) => (
                                        <ComparisonRow
                                            key={attribute.id}
                                            label={attribute.name}
                                            values={properties.map(
                                                (property) =>
                                                    property.attributes[
                                                        attribute.id
                                                    ] ?? '—',
                                            )}
                                        />
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Imóveis', href: index() },
    { title: 'Comparar', href: '' },
];

PropertiesCompare.layout = {
    breadcrumbs,
};
