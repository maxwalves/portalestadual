<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title'                                   => 'Portal - Obras Estaduais',
    'title_prefix'                            => '',
    'title_postfix'                           => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only'                            => true,
    'use_full_favicon'                        => false,

    'favicon' => [
        'path' => 'images/logoPortal.png',
        'type' => 'image/png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts'                            => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'Obras Estaduais',
    'logo_img' => 'images/logoPortal.png',
    'logo_img_class' => 'brand-image img elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Paranacidade',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'images/loginPortalObrasEstaduais.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'img' => [
            'path' => 'images/logoPortal.png',
            'alt' => 'Paranacidade',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled'                        => true,
    'usermenu_header'                         => false,
    'usermenu_header_class'                   => 'bg-primary',
    'usermenu_image'                          => false,
    'usermenu_desc'                           => false,
    'usermenu_profile_url'                    => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav'                           => null,
    'layout_boxed'                            => null,
    'layout_fixed_sidebar'                    => null,
    'layout_fixed_navbar'                     => null,
    'layout_fixed_footer'                     => null,
    'layout_dark_mode'                        => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card'                       => 'card-outline card-primary',
    'classes_auth_header'                     => '',
    'classes_auth_body'                       => '',
    'classes_auth_footer'                     => '',
    'classes_auth_icon'                       => '',
    'classes_auth_btn'                        => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body'                            => '',
    'classes_brand'                           => 'navbar-light bg-light',
    'classes_brand_text'                      => 'text-dark',
    'classes_content_wrapper'                 => '',
    'classes_content_header'                  => '',
    'classes_content'                         => '',
    'classes_sidebar'                         => 'sidebar-light-primary elevation-2',
    'classes_sidebar_nav'                     => 'nav-flat nav-child-indent',
    'classes_topnav'                          => 'navbar-light bg-light',
    'classes_topnav_nav'                      => 'navbar-expand',
    'classes_topnav_container'                => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini'                            => 'lg',
    'sidebar_collapse'                        => false,
    'sidebar_collapse_auto_size'              => false,
    'sidebar_collapse_remember'               => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme'                 => 'os-theme-light',
    'sidebar_scrollbar_auto_hide'             => 'l',
    'sidebar_nav_accordion'                   => true,
    'sidebar_nav_animation_speed'             => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar'                           => false,
    'right_sidebar_icon'                      => 'fas fa-cogs',
    'right_sidebar_theme'                     => 'dark',
    'right_sidebar_slide'                     => true,
    'right_sidebar_push'                      => true,
    'right_sidebar_scrollbar_theme'           => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide'       => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url'                           => false,
    'dashboard_url'                           => 'dashboard',
    'logout_url'                              => 'logout',
    'login_url'                               => 'login',
    'register_url'                            => 'register',
    'password_reset_url'                      => 'password/reset',
    'password_email_url'                      => 'password/email',
    'profile_url'                             => false,
    'disable_darkmode_routes'                 => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling'                  => false,
    'laravel_css_path'                        => 'css/app.css',
    'laravel_js_path'                         => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu'                                    => [
        // Navbar items:
        [
            'type'         => 'navbar-search',
            'text'         => 'search',
            'topnav_right' => true,
        ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'text'        => 'Dashboard',
            'url'         => 'dashboard',
            'icon'        => 'fas fa-fw fa-tachometer-alt',
            'icon_color'  => 'primary',
        ],
        
        // ===== SEPARADOR: ADMINISTRAÇÃO =====
        [
            'header' => 'ADMINISTRAÇÃO',
            'can'    => ['admin', 'admin_paranacidade'],
        ],
        
        // Gestão de Usuários - Apenas Admin Sistema
        [
            'text'        => 'Usuários',
            'icon'        => 'fas fa-fw fa-users',
            'icon_color'  => 'red',
            'can'         => ['admin'],
            'url'         => 'admin/users/roles',
        ],
        
        // Organizações - Apenas Admins
        [
            'text'        => 'Organizações',
            'url'         => 'organizacoes',
            'icon'        => 'fas fa-building',
            'icon_color'  => 'red',
            'can'         => ['admin', 'admin_paranacidade'],
        ],
        
        // ===== SEPARADOR: GESTÃO DE OBRAS =====
        [
            'header' => 'GESTÃO DE OBRAS',
        ],
        
        // Termos de Adesão
        [
            'text'        => 'Termos de Adesão',
            'url'         => 'termos-adesao',
            'icon'        => 'fas fa-file-signature',
            'icon_color'  => 'green',
            'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
        ],
        
        // Demandas e Ações
        [
            'text'        => 'Demandas e Ações',
            'icon'        => 'fas fa-tasks',
            'icon_color'  => 'green',
            'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
            'submenu'     => [
                [
                    'text'        => 'Cadastro GMS',
                    'url'         => 'cadastros-demanda-gms',
                    'icon'        => 'fas fa-database',
                    'icon_color'  => 'green',
                    'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria'],
                ],
                [
                    'text'        => 'Demandas',
                    'url'         => 'demandas',
                    'icon'        => 'fas fa-clipboard-list',
                    'icon_color'  => 'green',
                    'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
                ],
                [
                    'text'        => 'Ações/Obras',
                    'url'         => 'acoes',
                    'icon'        => 'fas fa-rocket',
                    'icon_color'  => 'green',
                    'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
                ],
            ],
        ],
        
        // ===== SEPARADOR: DOCUMENTOS =====
        [
            'header' => 'DOCUMENTOS',
            'can'    => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
        ],
        
        // Gestão Documental
        [
            'text'        => 'Gestão Documental',
            'icon'        => 'fas fa-folder-open',
            'icon_color'  => 'blue',
            'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
            'submenu'     => [
                [
                    'text'        => 'Grupos de Exigências',
                    'url'         => 'grupo-exigencias',
                    'icon'        => 'fas fa-layer-group',
                    'icon_color'  => 'blue',
                    'can'         => ['admin', 'admin_paranacidade'],
                ],
                [
                    'text'        => 'Tipos de Documento',
                    'url'         => 'tipos-documento',
                    'icon'        => 'fas fa-file-alt',
                    'icon_color'  => 'blue',
                    'can'         => ['admin', 'admin_paranacidade'],
                ],
                [
                    'text'        => 'Templates',
                    'url'         => 'template-documentos',
                    'icon'        => 'fas fa-file-medical',
                    'icon_color'  => 'blue',
                    'can'         => ['admin', 'admin_paranacidade'],
                ],
                [
                    'text'        => 'Documentos',
                    'url'         => 'documentos',
                    'icon'        => 'fas fa-archive',
                    'icon_color'  => 'blue',
                    'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
                ],
            ],
        ],
        
        // ===== SEPARADOR: ACOMPANHAMENTO =====
        [
            'header' => 'ACOMPANHAMENTO',
            'can'    => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
        ],
        
        // Notificações
        [
            'text'        => 'Notificações',
            'url'         => 'notificacoes',
            'icon'        => 'fas fa-bell',
            'icon_color'  => 'yellow',
            'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
        ],
        
        // Histórico
        [
            'text'        => 'Histórico de Etapas',
            'url'         => 'historico-etapas',
            'icon'        => 'fas fa-history',
            'icon_color'  => 'yellow',
            'can'         => ['admin', 'admin_paranacidade', 'tecnico_paranacidade', 'admin_secretaria', 'tecnico_secretaria'],
        ],
        
        // ===== SEPARADOR: CONFIGURAÇÕES =====
        [
            'header' => 'CONFIGURAÇÕES',
            'can'    => ['admin', 'admin_paranacidade'],
        ],
        
        // Workflow
        [
            'text'        => 'Workflow',
            'icon'        => 'fas fa-cogs',
            'icon_color'  => 'purple',
            'can'         => ['admin', 'admin_paranacidade'],
            'submenu'     => [
                [
                    'text'        => 'Tipos de Fluxo',
                    'url'         => 'tipos-fluxo',
                    'icon'        => 'fas fa-route',
                    'icon_color'  => 'purple',
                    'can'         => ['admin', 'admin_paranacidade'],
                ],
                [
                    'text'        => 'Status',
                    'url'         => 'status',
                    'icon'        => 'fas fa-flag',
                    'icon_color'  => 'purple',
                    'can'         => ['admin', 'admin_paranacidade'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters'                                 => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Menu\Filters\RoleFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins'                                 => [
        'Datatables'  => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2'     => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs'     => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'CustomSidebar' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => true,
                    'location' => 'css/custom-sidebar.css',
                ],
            ],
        ],
        'Pace'        => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe'                                  => [
        'default_tab' => [
            'url'   => null,
            'title' => null,
        ],
        'buttons'     => [
            'close'           => true,
            'close_all'       => true,
            'close_all_other' => true,
            'scroll_left'     => true,
            'scroll_right'    => true,
            'fullscreen'      => true,
        ],
        'options'     => [
            'loading_screen'    => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items'  => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire'                                => false,

    /*
    |--------------------------------------------------------------------------
    | Custom CSS
    |--------------------------------------------------------------------------
    |
    | Here we can add custom CSS to override default styles.
    |
    */
    
    'custom_css' => '
        /* ===== PERSONALIZAÇÃO DO SIDEBAR ===== */
        
        /* Fundo do sidebar com gradiente claro */
        .main-sidebar {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            box-shadow: 2px 0 6px rgba(0,0,0,0.1) !important;
        }
        
        /* Links principais do menu - azul escuro */
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link {
            color: #1e3a8a !important;
            font-weight: 500;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            transition: all 0.3s ease;
        }
        
        /* Hover nos links principais */
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: #dbeafe !important;
            color: #1e40af !important;
            transform: translateX(2px);
        }
        
        /* Link ativo/selecionado */
        .sidebar-light-primary .nav-sidebar > .nav-item.menu-open > .nav-link,
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        
        /* Cabeçalhos das seções (separadores) */
        .sidebar-light-primary .nav-header {
            color: #374151 !important;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: rgba(59, 130, 246, 0.1) !important;
            margin: 0.75rem 0.5rem 0.5rem 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border-left: 3px solid #3b82f6;
        }
        
        /* Submenus (nav-treeview) */
        .sidebar-light-primary .nav-treeview > .nav-item > .nav-link {
            color: #4b5563 !important;
            padding-left: 2.5rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            margin: 0.125rem 0.5rem;
        }
        
        /* Hover nos submenus */
        .sidebar-light-primary .nav-treeview > .nav-item > .nav-link:hover {
            background-color: #f3f4f6 !important;
            color: #1e40af !important;
            padding-left: 2.75rem;
            transition: all 0.2s ease;
        }
        
        /* Submenu ativo */
        .sidebar-light-primary .nav-treeview > .nav-item > .nav-link.active {
            background-color: #60a5fa !important;
            color: #ffffff !important;
            font-weight: 500;
        }
        
        /* ===== ÁREA DA MARCA/LOGO ===== */
        .brand-link {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border-bottom: 2px solid #e5e7eb !important;
            padding: 1rem !important;
        }
        
        .brand-text {
            color: #1e3a8a !important;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* ===== SCROLLBAR PERSONALIZADA ===== */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* ===== ÍCONES COLORIDOS ===== */
        .nav-icon.text-red { color: #dc2626 !important; }
        .nav-icon.text-green { color: #16a34a !important; }
        .nav-icon.text-blue { color: #2563eb !important; }
        .nav-icon.text-yellow { color: #d97706 !important; }
        .nav-icon.text-purple { color: #9333ea !important; }
        .nav-icon.text-primary { color: #3b82f6 !important; }
        
        /* ===== ANIMAÇÕES SUAVES ===== */
        .nav-sidebar .nav-item {
            transition: all 0.2s ease;
        }
        
        .nav-sidebar .nav-link {
            transition: all 0.3s ease;
        }
    ',
];
