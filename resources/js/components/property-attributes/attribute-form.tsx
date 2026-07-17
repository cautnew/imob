import { Form } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
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
import type { RouteDefinition } from '@/wayfinder';

type TypeOption = {
    value: string;
    label: string;
};

const OPTION_TYPES = ['select', 'multiselect'];

export type PropertyAttributeFormValues = {
    name: string;
    type: string;
    filterable: boolean;
    comparable: boolean;
    required: boolean;
    options: string[];
};

type Props = {
    action: RouteDefinition<'post'> | RouteDefinition<'put'>;
    types: TypeOption[];
    defaultValues?: Partial<PropertyAttributeFormValues>;
    submitLabel: string;
    backHref: string;
};

export default function PropertyAttributeForm({
    action,
    types,
    defaultValues,
    submitLabel,
    backHref,
}: Props) {
    const [type, setType] = useState(defaultValues?.type ?? '');
    const [options, setOptions] = useState<string[]>(
        defaultValues?.options && defaultValues.options.length > 0
            ? defaultValues.options
            : [''],
    );

    const hasOptions = OPTION_TYPES.includes(type);

    const addOption = () => setOptions((current) => [...current, '']);

    const removeOption = (index: number) =>
        setOptions((current) => current.filter((_, i) => i !== index));

    const updateOption = (index: number, value: string) =>
        setOptions((current) =>
            current.map((option, i) => (i === index ? value : option)),
        );

    return (
        <Form
            action={action}
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
                            defaultValue={defaultValues?.name}
                            placeholder="Ex: Número de quartos"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="type">Tipo</Label>
                        <Select
                            name="type"
                            required
                            value={type}
                            onValueChange={setType}
                        >
                            <SelectTrigger id="type" className="w-full">
                                <SelectValue placeholder="Selecione um tipo" />
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

                    {hasOptions && (
                        <div className="grid gap-2">
                            <Label>Opções</Label>
                            <div className="flex flex-col gap-2">
                                {options.map((option, index) => (
                                    <div key={index} className="grid gap-1">
                                        <div className="flex items-center gap-2">
                                            <Input
                                                type="text"
                                                required
                                                name={`options[${index}][value]`}
                                                value={option}
                                                onChange={(e) =>
                                                    updateOption(
                                                        index,
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder={`Opção ${index + 1}`}
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                disabled={options.length <= 1}
                                                onClick={() =>
                                                    removeOption(index)
                                                }
                                            >
                                                <Trash2 />
                                                <span className="sr-only">
                                                    Remover opção
                                                </span>
                                            </Button>
                                        </div>
                                        <InputError
                                            message={
                                                errors[`options.${index}.value`]
                                            }
                                        />
                                    </div>
                                ))}
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="w-fit"
                                onClick={addOption}
                            >
                                <Plus />
                                Adicionar opção
                            </Button>
                            <InputError message={errors.options} />
                        </div>
                    )}

                    <div className="grid gap-3">
                        <Label>Flags</Label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                name="filterable"
                                value="1"
                                defaultChecked={defaultValues?.filterable}
                            />
                            Filtrável
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                name="comparable"
                                value="1"
                                defaultChecked={defaultValues?.comparable}
                            />
                            Comparável
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                name="required"
                                value="1"
                                defaultChecked={defaultValues?.required}
                            />
                            Obrigatório
                        </label>
                    </div>

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
