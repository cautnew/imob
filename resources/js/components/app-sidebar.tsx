import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Building2,
    DollarSign,
    FileSignature,
    FolderGit2,
    KeyRound,
    LayoutGrid,
    Receipt,
    ShieldCheck,
    SlidersHorizontal,
    Tags,
    UserRound,
    Users,
    UsersRound,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import { index as billsIndex } from '@/routes/bills';
import { index as featuresIndex } from '@/routes/features';
import { index as leasesIndex } from '@/routes/leases';
import { index as lesseesIndex } from '@/routes/lessees';
import { index as ownersIndex } from '@/routes/owners';
import { index as permissionsIndex } from '@/routes/permissions';
import { index as priceTypesIndex } from '@/routes/price-types';
import { index as propertiesIndex } from '@/routes/properties';
import { index as propertyAttributesIndex } from '@/routes/property-attributes';
import { index as rolesIndex } from '@/routes/roles';
import { index as transactionsIndex } from '@/routes/transactions';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/react-starter-kit',
    //     icon: FolderGit2,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#react',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { can } = usePermissions();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(can('imoveis.visualizar')
            ? [
                  {
                      title: 'Imóveis',
                      href: propertiesIndex(),
                      icon: Building2,
                  },
              ]
            : []),
        ...(can('proprietarios.visualizar')
            ? [
                  {
                      title: 'Proprietários',
                      href: ownersIndex(),
                      icon: UserRound,
                  },
              ]
            : []),
        ...(can('inquilinos.visualizar')
            ? [
                  {
                      title: 'Inquilinos',
                      href: lesseesIndex(),
                      icon: UsersRound,
                  },
              ]
            : []),
        ...(can('locacoes.visualizar')
            ? [
                  {
                      title: 'Locações',
                      href: leasesIndex(),
                      icon: FileSignature,
                  },
              ]
            : []),
        ...(can('financeiro.visualizar')
            ? [
                  {
                      title: 'Financeiro',
                      href: transactionsIndex(),
                      icon: Wallet,
                  },
              ]
            : []),
        ...(can('boletos.visualizar')
            ? [
                  {
                      title: 'Boletos',
                      href: billsIndex(),
                      icon: Receipt,
                  },
              ]
            : []),
        ...(can('usuarios.visualizar')
            ? [
                  {
                      title: 'Usuários',
                      href: usersIndex(),
                      icon: Users,
                  },
              ]
            : []),
        ...(can('papeis.visualizar')
            ? [
                  {
                      title: 'Papéis',
                      href: rolesIndex(),
                      icon: ShieldCheck,
                  },
              ]
            : []),
        ...(can('permissoes.visualizar')
            ? [
                  {
                      title: 'Permissões',
                      href: permissionsIndex(),
                      icon: KeyRound,
                  },
              ]
            : []),
        ...(can('caracteristicas.visualizar')
            ? [
                  {
                      title: 'Características',
                      href: featuresIndex(),
                      icon: Tags,
                  },
              ]
            : []),
        ...(can('atributos.visualizar')
            ? [
                  {
                      title: 'Atributos',
                      href: propertyAttributesIndex(),
                      icon: SlidersHorizontal,
                  },
              ]
            : []),
        ...(can('precos.visualizar')
            ? [
                  {
                      title: 'Tipos de preço',
                      href: priceTypesIndex(),
                      icon: DollarSign,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
