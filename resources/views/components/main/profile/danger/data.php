<?php

return [
    'defaultDangerActions' => [
        [
            'id' => 'logout-all',
            'title' => 'Logout all devices',
            'description' => 'Sign out from every active session.',
            'buttonText' => 'Logout',
            'variant' => 'outline',
            'icon' => 'log-out',
            'iconClass' => 'size-5 rtl:rotate-180',
        ],
        [
            'id' => 'delete-account',
            'title' => 'Delete account',
            'description' => 'Permanently remove your account and all data.',
            'buttonText' => 'Delete',
            'variant' => 'danger-outline',
            'icon' => 'trash-bin',
            'iconClass' => 'size-5',
        ],
    ],
];
