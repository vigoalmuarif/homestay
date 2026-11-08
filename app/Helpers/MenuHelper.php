<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems()
    {
        $items = [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/',
                'permission' => 'view dashboard',
            ],
            // [
            //     'icon' => 'calendar',
            //     'name' => 'Kalender Okupansi',
            //     'path' => '/calendar',
            //     'permission' => 'view bookings',
            // ],
            [
                'icon' => 'task',
                'name' => 'Reservasi & Front Desk',
                'path' => '/bookings',
                'permission' => 'view bookings',
            ],
            [
                'icon' => 'user-profile',
                'name' => 'Data Tamu (Customer)',
                'path' => '/customers',
                'permission' => 'view customers',
            ],
            [
                'icon' => 'layout',
                'name' => 'Kamar & Tipe Kamar',
                'subItems' => [
                    ['name' => 'Daftar Kamar Fisik', 'path' => '/rooms', 'permission' => 'view rooms'],
                    ['name' => 'Katalog Tipe Kamar', 'path' => '/room-types', 'permission' => 'manage rooms'],
                ],
            ],
            [
                'icon' => 'ecommerce',
                'name' => 'Layanan & F&B',
                'subItems' => [
                    ['name' => 'Klaim Inklusi & Fulfillment', 'path' => '/inclusions/claim-desk', 'permission' => 'view room-orders'],
                    ['name' => 'Katalog Menu & POS', 'path' => '/services', 'permission' => 'view services'],
                    ['name' => 'Inklusi Paket Kamar', 'path' => '/service-inclusions', 'permission' => 'manage services'],
                ],
            ],
            // [
            //     'icon' => 'empty-state',
            //     'name' => 'Housekeeping',
            //     'path' => '/housekeeping',
            //     'permission' => 'view housekeeping',
            // ],
            [
                'icon' => 'charts',
                'name' => 'Keuangan & Laporan',
                'subItems' => [
                    // ['name' => 'Folio Billing & Kasir', 'path' => '/billing', 'permission' => 'view billing'],
                    ['name' => 'Laporan Rekapitulasi', 'path' => '/reports', 'permission' => 'view reports'],
                ],
            ],
            [
                'icon' => 'users',
                'name' => 'Kelola Petugas & Role',
                'path' => '/users',
                'role' => 'admin',
            ],
        ];

        return self::filterAccessibleItems($items);
    }

    public static function getOthersItems()
    {
        $items = [
            [
                'icon' => 'dashboard',
                'name' => 'Demo E-Commerce',
                'path' => '/ecommerce',
                'role' => 'admin',
            ],
            [
                'icon' => 'user-profile',
                'name' => 'User Profile',
                'path' => '/profile',
            ],
            [
                'name' => 'Form Elements',
                'icon' => 'forms',
                'path' => '/form-elements',
                'role' => 'admin',
            ],
            [
                'name' => 'Basic Tables',
                'icon' => 'tables',
                'path' => '/basic-tables',
                'role' => 'admin',
            ],
            [
                'name' => 'Charts',
                'icon' => 'charts',
                'role' => 'admin',
                'subItems' => [
                    ['name' => 'Line Chart', 'path' => '/line-chart', 'pro' => false],
                    ['name' => 'Bar Chart', 'path' => '/bar-chart', 'pro' => false],
                ],
            ],
            [
                'name' => 'UI Elements',
                'icon' => 'ui-elements',
                'role' => 'admin',
                'subItems' => [
                    ['name' => 'Alerts', 'path' => '/alerts', 'pro' => false],
                    ['name' => 'Avatar', 'path' => '/avatars', 'pro' => false],
                    ['name' => 'Badge', 'path' => '/badge', 'pro' => false],
                    ['name' => 'Buttons', 'path' => '/buttons', 'pro' => false],
                    ['name' => 'Images', 'path' => '/images', 'pro' => false],
                    ['name' => 'Videos', 'path' => '/videos', 'pro' => false],
                ],
            ],
        ];

        return self::filterAccessibleItems($items);
    }

    /**
     * Filter menu items and nested sub-items based on user permissions and roles.
     */
    protected static function filterAccessibleItems(array $items): array
    {
        $accessible = [];
        $user = auth()->user();

        foreach ($items as $item) {
            // Check top-level role/permission restriction
            if (!empty($item['role']) && (!$user || !$user->hasRole($item['role']))) {
                continue;
            }
            if (!empty($item['permission']) && (!$user || !$user->can($item['permission']))) {
                continue;
            }

            // Check nested sub-items
            if (isset($item['subItems']) && is_array($item['subItems'])) {
                $filteredSubs = array_values(array_filter($item['subItems'], function ($sub) use ($user) {
                    if (!empty($sub['role']) && (!$user || !$user->hasRole($sub['role']))) {
                        return false;
                    }
                    if (!empty($sub['permission']) && (!$user || !$user->can($sub['permission']))) {
                        return false;
                    }
                    return true;
                }));

                // If menu item has no accessible sub-items and no own path, hide it
                if (empty($filteredSubs) && empty($item['path'])) {
                    continue;
                }

                $item['subItems'] = $filteredSubs;
            }

            $accessible[] = $item;
        }

        return $accessible;
    }

    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Menu Utama',
                'items' => self::getMainNavItems(),
            ],
            // [
            //     'title' => 'Lainnya',
            //     'items' => self::getOthersItems(),
            // ],
        ];
    }

    /**
     * Get SVG HTML for a menu icon.
     */
    public static function getIconSvg(string $icon): string
    {
        $map = [
            'dashboard' => 'pie-chart',
            'user-profile' => 'user-circle',
            'layout' => 'box',
            'ecommerce' => 'target',
            'empty-state' => 'trash-bin',
            'charts' => 'bar-chart',
            'forms' => 'pencil',
            'tables' => 'table',
            'users' => 'users',
            'task' => 'task',
            'calendar' => 'calendar',
        ];

        $target = $map[$icon] ?? $icon;

        if (view()->exists("components.svg.{$target}")) {
            return \Illuminate\Support\Facades\Blade::render("<x-svg.{$target} class=\"size-5\" />");
        }

        if (view()->exists("components.svg.{$icon}")) {
            return \Illuminate\Support\Facades\Blade::render("<x-svg.{$icon} class=\"size-5\" />");
        }

        return '<svg class="size-5 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>';
    }
}
