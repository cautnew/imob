import {
    Building2,
    CalendarCheck,
    FileSignature,
    LineChart,
    ShieldCheck,
    Users,
} from 'lucide-react';
import SectionHeading from '@/components/public/section-heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const features = [
    {
        icon: Building2,
        title: 'Gestão de imóveis',
        description:
            'Cadastre imóveis com fotos, características e status atualizado em tempo real para toda a equipe.',
    },
    {
        icon: Users,
        title: 'Gestão de equipe',
        description:
            'Convide corretores, defina permissões e acompanhe o desempenho de cada membro da imobiliária.',
    },
    {
        icon: ShieldCheck,
        title: 'Multi-imobiliária',
        description:
            'Cada imobiliária tem seu próprio espaço, com dados isolados e configurações independentes.',
    },
    {
        icon: CalendarCheck,
        title: 'Agenda de visitas',
        description:
            'Organize visitas por corretor e imóvel, evitando conflitos de horário e perda de oportunidades.',
    },
    {
        icon: FileSignature,
        title: 'Propostas e contratos',
        description:
            'Acompanhe propostas do primeiro contato até a assinatura, sem perder o histórico da negociação.',
    },
    {
        icon: LineChart,
        title: 'Relatórios e indicadores',
        description:
            'Veja o que está funcionando com indicadores de vendas, visitas e conversão por corretor.',
    },
];

export default function FeaturesSection() {
    return (
        <section id="recursos" className="border-b border-sidebar-border/50">
            <div className="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <SectionHeading
                    eyebrow="Recursos"
                    title="Tudo o que sua imobiliária precisa, em um só sistema"
                    description="Menos planilhas e grupos de mensagens, mais controle sobre imóveis, equipe e negociações."
                />

                <div className="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {features.map(({ icon: Icon, title, description }) => (
                        <Card key={title} className="gap-4">
                            <CardHeader>
                                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Icon className="size-5" />
                                </div>
                                <CardTitle className="text-lg">
                                    {title}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
