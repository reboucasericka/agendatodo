<?php

$pagePaths = [
    resource_path('js/pages'),
];

$pageExtensions = [
    'js',
    'jsx',
    'svelte',
    'ts',
    'tsx',
    'vue',
];

return [

    /*
    |--------------------------------------------------------------------------
    | Initial page (Inertia.js v2+ / @inertiajs/vue3)
    |--------------------------------------------------------------------------
    |
    | O cliente Vue 3 lê a página inicial de <script type="application/json" data-page>.
    | Sem isto, o Blade antigo (<div data-page>) deixa getInitialPageFromDOM a null → tela em branco.
    |
    */

    'use_script_element_for_initial_page' => true,

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render each initial request made to your application's pages
    | so that server rendered HTML is delivered for the user's browser.
    |
    | See: https://inertiajs.com/server-side-rendering
    |
    */

    'ssr' => [
        'enabled' => (bool) env('INERTIA_SSR_ENABLED', false),
        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),
        // 'bundle' => base_path('bootstrap/ssr/ssr.mjs'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages (filesystem discovery for Inertia)
    |--------------------------------------------------------------------------
    |
    | Root keys page_paths / page_extensions are used by inertia-laravel.
    | The `pages` array mirrors the same values for clarity in one place.
    |
    */

    'page_paths' => $pagePaths,

    'page_extensions' => $pageExtensions,

    'pages' => [

        'paths' => $pagePaths,

        'extensions' => $pageExtensions,

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | Must include page_paths and page_extensions: a short `testing` array alone
    | replaces the package defaults entirely and breaks assertInertia (null paths).
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => $pagePaths,

        'page_extensions' => $pageExtensions,

    ],

];
