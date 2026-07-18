import { Link } from '@inertiajs/react';
import { ArrowRight, Building2, CalendarCheck, Users } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { login, register } from '@/routes';
import { login as portalLogin } from '@/routes/portal';

export default function HeroSection() {
    return (
        <section className="overflow-hidden border-b border-sidebar-border/50">
            <div className="mx-auto grid max-w-6xl items-center gap-12 px-6 py-16 lg:grid-cols-2 lg:py-24">
                <div className="flex flex-col items-start gap-6">
                    <Badge variant="secondary" className="px-3 py-1">
                        Feito para imobiliárias brasileiras
                    </Badge>

                    <h1 className="text-4xl font-bold tracking-tight text-balance sm:text-5xl">
                        A gestão da sua imobiliária, do jeito que ela merece.
                    </h1>

                    <p className="max-w-xl text-lg text-balance text-muted-foreground">
                        Centralize imóveis, equipe, visitas e propostas em um só
                        lugar. Um sistema multi-imobiliária pensado para
                        corretores que querem vender mais e administrar menos.
                    </p>

                    <div className="flex flex-col gap-3 sm:flex-row">
                        <Button asChild size="lg">
                            <Link href={register()}>
                                Criar conta grátis
                                <ArrowRight />
                            </Link>
                        </Button>
                        <Button asChild size="lg" variant="outline">
                            <Link href={login()}>Já tenho uma conta</Link>
                        </Button>
                    </div>

                    <p className="text-sm text-muted-foreground">
                        Não é necessário cartão de crédito para começar.
                    </p>

                    <p className="text-sm text-muted-foreground">
                        É inquilino?{' '}
                        <Link
                            href={portalLogin()}
                            className="font-medium text-foreground underline underline-offset-4 hover:text-primary"
                        >
                            Acesse o Portal do Inquilino
                        </Link>
                    </p>
                </div>

                <Card className="relative gap-4 border-sidebar-border/50 bg-card/60 p-6 shadow-lg backdrop-blur">
                    <div className="flex items-center justify-between">
                        <span className="text-sm font-semibold">
                            Painel da imobiliária
                        </span>
                        <Badge variant="outline">Visão geral</Badge>
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        {[
                            {
                                icon: Building2,
                                label: 'Imóveis ativos',
                                value: '128',
                            },
                            {
                                icon: Users,
                                label: 'Corretores',
                                value: '14',
                            },
                            {
                                icon: CalendarCheck,
                                label: 'Visitas na semana',
                                value: '32',
                            },
                        ].map(({ icon: Icon, label, value }) => (
                            <div
                                key={label}
                                className="flex flex-col gap-2 rounded-lg border border-sidebar-border/50 bg-background p-4"
                            >
                                <Icon className="size-5 text-primary" />
                                <span className="text-2xl font-bold">
                                    {value}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {label}
                                </span>
                            </div>
                        ))}
                    </div>

                    <div className="flex flex-col gap-2 rounded-lg border border-sidebar-border/50 bg-background p-4">
                        {[
                            'Apartamento 2 quartos — Jardim Europa',
                            'Casa em condomínio — Alphaville',
                            'Sala comercial — Centro',
                        ].map((item) => (
                            <div
                                key={item}
                                className="flex items-center justify-between border-b border-sidebar-border/40 py-2 text-sm last:border-0 last:pb-0"
                            >
                                <span className="text-muted-foreground">
                                    {item}
                                </span>
                                <Badge variant="secondary">Disponível</Badge>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>
        </section>
    );
}
