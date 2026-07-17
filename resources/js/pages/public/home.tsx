import { Head } from '@inertiajs/react';
import BenefitsSection from '@/components/public/benefits-section';
import CtaSection from '@/components/public/cta-section';
import DemoSection from '@/components/public/demo-section';
import FaqSection from '@/components/public/faq-section';
import FeaturesSection from '@/components/public/features-section';
import HeroSection from '@/components/public/hero-section';

export default function PublicHome() {
    return (
        <>
            <Head title="Sistema de gestão para imobiliárias" />

            <HeroSection />
            <FeaturesSection />
            <DemoSection />
            <BenefitsSection />
            <FaqSection />
            <CtaSection />
        </>
    );
}
