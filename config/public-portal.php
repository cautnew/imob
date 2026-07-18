<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reserved Company Slugs
    |--------------------------------------------------------------------------
    |
    | Every literal top-level URI segment already registered elsewhere in
    | routes/*.php (plus infrastructure paths and the public portal's own
    | reserved words) so a company slug can never shadow an existing route.
    | Keep in sync with public/robots.txt's Disallow list.
    |
    */
    'reserved_slugs' => [
        '', 'home', 'sobre', 'contato', 'dashboard', 'settings', 'primeiro-acesso',
        'usuarios', 'papeis', 'permissoes', 'categorias-caracteristicas', 'caracteristicas',
        'atributos', 'precos', 'imoveis', 'proprietarios', 'inquilinos', 'locacoes',
        'categorias-financeiras', 'financeiro', 'boletos', 'notificacoes', 'portal',
        'up', 'api', 'storage', 'build',
        'favoritos', 'comparacao', 'busca', 'sitemap.xml', 'sitemap', 'robots.txt', 'login', 'logout', 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Comparison Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of properties a visitor may hold in the comparison
    | cookie at once.
    |
    */
    'comparison_max' => 4,

    /*
    |--------------------------------------------------------------------------
    | Favorites Cookie Lifetime
    |--------------------------------------------------------------------------
    */
    'favorites_cookie_ttl_days' => 90,
];
