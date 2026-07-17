import { Head } from '@inertiajs/react';
import { Heart, Sparkles, Target } from 'lucide-react';
import CtaSection from '@/components/public/cta-section';
import PageHeader from '@/components/public/page-header';
import SectionHeading from '@/components/public/section-heading';

const values = [
    {
        icon: Target,
        title: 'Foco no corretor',
        description:
            'Cada decisão de produto parte de um problema real do dia a dia de quem vende imóveis.',
    },
    {
        icon: Sparkles,
        title: 'Simplicidade',
        description:
            'Preferimos um sistema fácil de usar hoje a um sistema completo que ninguém usa.',
    },
    {
        icon: Heart,
        title: 'Parceria de verdade',
        description:
            'Crescemos junto com as imobiliárias que usam o sistema, ouvindo o feedback de cada equipe.',
    },
];

export default function PublicAbout() {
    return (
        <>
            <Head title="Sobre" />

            <PageHeader
                eyebrow="Sobre nós"
                title="Construído por quem entende o mercado imobiliário"
                description="Nascemos da necessidade de trocar planilhas e grupos de mensagens por um sistema simples, feito para o dia a dia da imobiliária."
            />

            <section className="border-b border-sidebar-border/50">
                <div className="mx-auto max-w-3xl px-6 py-16 lg:py-24">
                    <SectionHeading
                        eyebrow="Nossa história"
                        title="De uma planilha bagunçada a um sistema completo"
                        align="left"
                    />
                    <div className="mt-6 flex flex-col gap-4 text-muted-foreground">
                        <p>
                            Percebemos que boa parte das imobiliárias ainda
                            organiza imóveis, visitas e propostas em planilhas
                            soltas e conversas de WhatsApp. Isso funciona até a
                            equipe crescer — e então a informação se perde.
                        </p>
                        <p>
                            Criamos este sistema para dar a cada imobiliária um
                            espaço próprio, com sua equipe, seus imóveis e suas
                            negociações organizados em um único lugar, sem
                            depender de planilhas espalhadas ou processos
                            manuais.
                        </p>
                    </div>
                </div>
            </section>

            <section className="border-b border-sidebar-border/50">
                <div className="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                    <SectionHeading
                        eyebrow="O que nos guia"
                        title="Nossos valores"
                    />

                    <div className="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-3">
                        {values.map(({ icon: Icon, title, description }) => (
                            <div
                                key={title}
                                className="flex flex-col items-center gap-3 text-center"
                            >
                                <span className="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <Icon className="size-6" />
                                </span>
                                <p className="font-semibold">{title}</p>
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <CtaSection />
        </>
    );
}
