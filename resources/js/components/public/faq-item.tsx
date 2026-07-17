import { ChevronDown } from 'lucide-react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

export default function FaqItem({
    question,
    answer,
    defaultOpen = false,
}: {
    question: string;
    answer: string;
    defaultOpen?: boolean;
}) {
    return (
        <Collapsible
            defaultOpen={defaultOpen}
            className="group rounded-lg border border-sidebar-border/50"
        >
            <CollapsibleTrigger className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-medium">
                {question}
                <ChevronDown className="size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
            </CollapsibleTrigger>
            <CollapsibleContent className="px-5 pb-4 text-sm text-muted-foreground">
                {answer}
            </CollapsibleContent>
        </Collapsible>
    );
}
