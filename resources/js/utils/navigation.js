export const navigationItems = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        icon: 'fa-solid fa-chart-line',
        permissions: ['dashboard.view'],
    },
    {
        name: 'inventory',
        label: 'Inventario',
        icon: 'fa-solid fa-boxes-stacked',
        permissions: ['inventory.view'],
        disabled: true,
    },
    {
        name: 'purchases',
        label: 'Compras',
        icon: 'fa-solid fa-truck-ramp-box',
        permissions: ['purchases.view'],
        disabled: true,
    },
    {
        name: 'pos',
        label: 'POS',
        icon: 'fa-solid fa-cash-register',
        permissions: ['pos.access'],
        disabled: true,
    },
    {
        name: 'reports',
        label: 'Reportes',
        icon: 'fa-solid fa-file-export',
        permissions: ['reports.view'],
        disabled: true,
    },
];