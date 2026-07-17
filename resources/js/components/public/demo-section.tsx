import { CheckCircle2 } from 'lucide-react';
import SectionHeading from '@/components/public/section-heading';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';

const steps = [
    {
        title: 'Cadastre sua imobiliária',
        description:
            'Crie sua conta, informe os dados da imobiliária e convide sua equipe em poucos minutos.',
    },
    {
        title: 'Adicione seus imóveis',
        description:
            'Registre os imóveis com fotos e informações completas para deixá-los prontos para negociação.',
    },
    {
        title: 'Acompanhe cada negociação',
        description:
            'Do agendamento da visita à assinatura do contrato, tudo em um único painel para toda a equipe.',
    },
];

export default function DemoSection() {
    return (
        <section
            id="demonstracao"
            className="border-b border-sidebar-border/50 bg-muted/30"
        >
            <div className="mx-auto grid max-w-6xl items-center gap-12 px-6 py-16 lg:grid-cols-2 lg:py-24">
                <div className="flex flex-col gap-8">
                    <SectionHeading
                        eyebrow="Demonstração"
                        title="Veja como funciona na prática"
                        description="Um fluxo simples, pensado para o dia a dia de quem já usa planilhas, papel e grupos de WhatsApp."
                        align="left"
                    />

                    <ol className="flex flex-col gap-6">
                        {steps.map((step, index) => (
                            <li key={step.title} className="flex gap-4">
                                <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                                    {index + 1}
                                </span>
                                <div>
                                    <p className="font-semibold">
                                        {step.title}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {step.description}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ol>
                </div>

                <Card className="gap-0 overflow-hidden border-sidebar-border/50 p-0 shadow-lg">
                    <div className="flex items-center gap-1.5 border-b border-sidebar-border/50 bg-muted/50 px-4 py-3">
                        <span className="size-2.5 rounded-full bg-destructive/60" />
                        <span className="size-2.5 rounded-full bg-yellow-500/60" />
                        <span className="size-2.5 rounded-full bg-green-500/60" />
                        <span className="ml-3 text-xs text-muted-foreground">
                            app.suaimobiliaria.com.br
                        </span>
                    </div>

                    <div className="flex flex-col gap-4 p-6">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold">
                                Negociações em andamento
                            </span>
                            <Badge variant="secondary">Esta semana</Badge>
                        </div>

                        {[
                            {
                                title: 'Apartamento 3 quartos — Vila Nova',
                                stage: 'Proposta enviada',
                            },
                            {
                                title: 'Casa térrea — Bela Vista',
                                stage: 'Visita agendada',
                            },
                            {
                                title: 'Cobertura duplex — Centro',
                                stage: 'Contrato assinado',
                            },
                        ].map((deal) => (
                            <div
                                key={deal.title}
                                className="flex items-center justify-between gap-3 rounded-lg border border-sidebar-border/50 p-3"
                            >
                                <div className="flex items-center gap-3">
                                    <CheckCircle2 className="size-4 shrink-0 text-primary" />
                                    <span className="text-sm">
                                        {deal.title}
                                    </span>
                                </div>
                                <Badge variant="outline" className="shrink-0">
                                    {deal.stage}
                                </Badge>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>
        </section>
    );
}
