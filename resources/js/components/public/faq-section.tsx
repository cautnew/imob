import FaqItem from '@/components/public/faq-item';
import SectionHeading from '@/components/public/section-heading';

const faqs = [
    {
        question: 'Posso cadastrar mais de uma imobiliária?',
        answer: 'Sim. O sistema é multi-imobiliária: cada conta gerencia sua própria imobiliária, com dados e equipe isolados das demais.',
    },
    {
        question: 'Quantos corretores posso convidar para a equipe?',
        answer: 'Não há limite fixo de corretores. Convide toda a sua equipe e defina o nível de acesso de cada pessoa.',
    },
    {
        question: 'Preciso instalar algum programa?',
        answer: 'Não. O sistema funciona direto do navegador, em qualquer computador ou celular com acesso à internet.',
    },
    {
        question: 'Tem período de teste gratuito?',
        answer: 'Sim. Você pode criar sua conta e conhecer o sistema sem precisar informar um cartão de crédito.',
    },
    {
        question: 'Meus dados ficam seguros?',
        answer: 'Sim. Cada imobiliária acessa apenas os próprios dados, e o acesso da equipe é controlado por permissões.',
    },
];

export default function FaqSection() {
    return (
        <section id="faq" className="border-b border-sidebar-border/50">
            <div className="mx-auto max-w-3xl px-6 py-16 lg:py-24">
                <SectionHeading
                    eyebrow="Perguntas frequentes"
                    title="Ainda com dúvidas?"
                    description="Se não encontrar sua resposta aqui, fale com a gente na página de contato."
                />

                <div className="mt-10 flex flex-col gap-3">
                    {faqs.map((faq, index) => (
                        <FaqItem
                            key={faq.question}
                            question={faq.question}
                            answer={faq.answer}
                            defaultOpen={index === 0}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}
