import { Check } from 'lucide-react';
import SectionHeading from '@/components/public/section-heading';

const benefits = [
    {
        title: 'Menos tempo administrativo',
        description:
            'Reduza o tempo gasto organizando planilhas e mensagens para focar no que importa: vender.',
    },
    {
        title: 'Equipe sempre alinhada',
        description:
            'Todos os corretores enxergam o mesmo status dos imóveis e negociações, sem informação perdida.',
    },
    {
        title: 'Escale sem complicação',
        description:
            'Cresça de uma para várias imobiliárias sem precisar trocar de sistema ou duplicar processos.',
    },
    {
        title: 'Dados isolados e seguros',
        description:
            'Cada imobiliária acessa apenas os seus próprios dados, com controle de permissões por usuário.',
    },
];

export default function BenefitsSection() {
    return (
        <section id="beneficios" className="border-b border-sidebar-border/50">
            <div className="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <SectionHeading
                    eyebrow="Benefícios"
                    title="Feito para quem vive o dia a dia da imobiliária"
                />

                <div className="mt-12 grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2">
                    {benefits.map((benefit) => (
                        <div key={benefit.title} className="flex gap-4">
                            <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <Check className="size-4" />
                            </span>
                            <div>
                                <p className="font-semibold">{benefit.title}</p>
                                <p className="text-sm text-muted-foreground">
                                    {benefit.description}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
