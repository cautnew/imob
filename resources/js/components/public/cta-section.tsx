import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

export default function CtaSection() {
    return (
        <section className="mx-auto max-w-6xl px-6 py-16 lg:py-24">
            <div className="flex flex-col items-center gap-6 rounded-2xl bg-primary px-6 py-16 text-center text-primary-foreground">
                <h2 className="max-w-2xl text-3xl font-bold text-balance sm:text-4xl">
                    Pronto para organizar sua imobiliária?
                </h2>
                <p className="max-w-xl text-balance text-primary-foreground/80">
                    Crie sua conta gratuitamente e comece a cadastrar seus
                    imóveis e sua equipe hoje mesmo.
                </p>
                <Button asChild size="lg" variant="secondary">
                    <Link href={register()}>
                        Criar conta grátis
                        <ArrowRight />
                    </Link>
                </Button>
            </div>
        </section>
    );
}
