import { Head } from '@inertiajs/react';
import { Mail, MapPin, Phone } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import PageHeader from '@/components/public/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

const contactInfo = [
    {
        icon: Mail,
        label: 'E-mail',
        value: 'contato@suaimobiliaria.com.br',
    },
    {
        icon: Phone,
        label: 'Telefone',
        value: '(11) 4000-0000',
    },
    {
        icon: MapPin,
        label: 'Endereço',
        value: 'São Paulo, SP',
    },
];

export default function PublicContact() {
    const [sending, setSending] = useState(false);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);

        setTimeout(() => {
            setSending(false);
            event.currentTarget.reset();
            toast.success('Mensagem enviada! Em breve entraremos em contato.');
        }, 600);
    }

    return (
        <>
            <Head title="Contato" />

            <PageHeader
                eyebrow="Contato"
                title="Vamos conversar sobre sua imobiliária"
                description="Tire suas dúvidas ou peça uma demonstração personalizada para a sua equipe."
            />

            <section className="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div className="grid grid-cols-1 gap-12 lg:grid-cols-5">
                    <div className="flex flex-col gap-6 lg:col-span-2">
                        {contactInfo.map(({ icon: Icon, label, value }) => (
                            <Card
                                key={label}
                                className="flex-row items-center gap-4 p-5"
                            >
                                <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Icon className="size-5" />
                                </span>
                                <CardContent className="p-0">
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="font-medium">{value}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    <Card className="lg:col-span-3">
                        <CardContent>
                            <form
                                onSubmit={handleSubmit}
                                className="flex flex-col gap-5"
                            >
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Nome</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="Seu nome"
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">E-mail</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            required
                                            placeholder="voce@imobiliaria.com.br"
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="subject">Assunto</Label>
                                    <Input
                                        id="subject"
                                        name="subject"
                                        required
                                        placeholder="Como podemos ajudar?"
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="message">Mensagem</Label>
                                    <Textarea
                                        id="message"
                                        name="message"
                                        required
                                        rows={5}
                                        placeholder="Conte um pouco sobre a sua imobiliária"
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full sm:w-fit"
                                    disabled={sending}
                                >
                                    {sending && <Spinner />}
                                    Enviar mensagem
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </section>
        </>
    );
}
