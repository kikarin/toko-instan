<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Base Domain
    |--------------------------------------------------------------------------
    |
    | The root domain used for tenant subdomains, e.g. `toko-instan.test` or
    | `platform.com`. Storefronts are served at `{slug}.{base_domain}`.
    | Leave empty to infer subdomains from the request host.
    |
    */

    'base_domain' => env('PLATFORM_BASE_DOMAIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Reserved Host Labels
    |--------------------------------------------------------------------------
    |
    | Host labels that are never treated as a tenant subdomain.
    |
    */

    'reserved_labels' => ['www', 'app'],

];
