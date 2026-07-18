import { Link, router, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { show as showBill } from '@/routes/bills';
import { read, readAll } from '@/routes/notifications';

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    });

export function NotificationBell() {
    const { notifications } = usePage().props;

    if (!notifications) {
        return null;
    }

    const { unread_count, items } = notifications;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="relative h-9 w-9"
                >
                    <Bell className="size-5" />
                    {unread_count > 0 && (
                        <span className="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-destructive text-[10px] text-white">
                            {unread_count > 9 ? '9+' : unread_count}
                        </span>
                    )}
                    <span className="sr-only">Notificações</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-80" align="end">
                <div className="flex items-center justify-between px-2 py-1.5">
                    <DropdownMenuLabel className="p-0">
                        Notificações
                    </DropdownMenuLabel>
                    {unread_count > 0 && (
                        <button
                            type="button"
                            className="text-xs text-muted-foreground hover:underline"
                            onClick={() =>
                                router.patch(
                                    readAll().url,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                        >
                            Marcar todas como lidas
                        </button>
                    )}
                </div>
                <DropdownMenuSeparator />
                {items.length === 0 ? (
                    <p className="px-2 py-4 text-center text-sm text-muted-foreground">
                        Nenhuma notificação ainda.
                    </p>
                ) : (
                    items.map((item) => (
                        <DropdownMenuItem key={item.id} asChild>
                            <Link
                                href={showBill(item.data.bill_id)}
                                onClick={() => {
                                    if (!item.read_at) {
                                        router.patch(
                                            read(item.id).url,
                                            {},
                                            { preserveScroll: true },
                                        );
                                    }
                                }}
                                className="flex flex-col items-start gap-0.5 whitespace-normal"
                            >
                                <span
                                    className={
                                        item.read_at
                                            ? 'text-muted-foreground'
                                            : 'font-medium'
                                    }
                                >
                                    {item.data.message}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {formatDateTime(item.created_at)}
                                </span>
                            </Link>
                        </DropdownMenuItem>
                    ))
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
