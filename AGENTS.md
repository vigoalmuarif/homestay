# AGENTS.md — TailAdmin Free Laravel

> Free Laravel 12 admin dashboard template · RTL Layout Support · Tailwind CSS v4 · Blade Components · Alpine.js · Vite · ApexCharts · FullCalendar · Flatpickr

## Repo Map

```
app/
├── Helpers/                  # MenuHelper (sidebar menu groups, icons)
├── Http/                     # Controllers (Locale, Sidebar, Dashboard) and Middleware (SetLocale, TrustProxies)
├── Support/                  # AsChild, I18n, TwMerge and Ui/*Variants helpers for the <x-ui.*> primitives
└── View/Components/          # Class-based Blade components (svg, common, header)
bootstrap/
├── app.php                   # Laravel application configuration & middleware setup
config/
├── page-meta.php             # Per-route page <title> and meta description
lang/                         # English UI strings (en.json)
public/
└── assets/images/            # Static images, user avatars, icons, flags
resources/
├── css/
│   └── app.css               # Tailwind CSS v4 theme (@theme), global utilities & 3rd party overrides
├── js/
│   ├── app.js                # Main JS entry point (Alpine.js, component dynamic imports)
│   ├── bootstrap.js          # Axios & HTTP setup
│   ├── components/           # Client-side modules (calendar, chart, forms, floating, maps, kanban, data-table)
│   └── ui/                   # Alpine behaviour for the <x-ui.*> primitives
└── views/
    ├── components/
    │   ├── ui/               # UI primitives (<x-ui.button>, <x-ui.dropdown-menu>, ...)
    │   ├── common/           # Shared page elements (page-breadcrumb, component-card, common-grid-shape)
    │   ├── header/           # Header widgets (user-dropdown, notification-dropdown)
    │   ├── svg/              # Reusable icons (<x-svg.icon-name />)
    │   ├── ui-elements/      # UI Elements demo blocks
    │   └── main/             # Feature components: calendar, dashboard, forms, full-width-pages, pages, profile, tables, others
    ├── layouts/              # Master layouts (app.blade.php, fullscreen-layout.blade.php, sidebar.blade.php, app-header.blade.php)
    └── pages/                # Route view templates (dashboard/, forms/, tables/, chart/, ui-elements/, errors/, auth/, pages/, calendar, profile)
routes/
├── web.php                   # Web application routes
└── console.php               # Console commands
```

## Stack

- **Laravel 12** with **PHP >= 8.2**.
- **Blade Template Engine** using modular, component-driven architecture (`<x-component-name />`).
- **Tailwind CSS v4** configured with `@tailwindcss/vite` and `@theme` tokens in `resources/css/app.css`.
- **Alpine.js v3** for reactive UI interactions, toggles, dropdowns, and global store management (`Alpine.store('theme')`).
- **Vite 7** with `laravel-vite-plugin` for lightning-fast asset compilation.
- **Third-Party Libraries**: ApexCharts, FullCalendar, Flatpickr, jsVectorMap, Prism.js.
- Scripts:
  - `composer run dev` — runs `php artisan serve`, `npm run dev`, `php artisan pail` concurrently.
  - `npm run dev` / `npm run build` — Vite development and production asset bundling.
  - `composer test` — runs test suite via Pest PHP.

## Conventions

- **New Page**:
  - Add route in `routes/web.php`.
  - Add the page title and meta description to `config/page-meta.php`.
  - Create Blade view in `resources/views/pages/<category>/<page-name>.blade.php`.
  - Extend the appropriate layout: `@extends('layouts.app')` for dashboard pages or `@extends('layouts.fullscreen-layout')` for auth/error/utility pages.
- **New Reusable Component**:
  - Feature component: `resources/views/components/main/<feature>/<component-name>.blade.php`, used as `<x-main.<feature>.<component-name> />`.
  - Generic UI primitive: `resources/views/components/ui/<component-name>.blade.php`.
  - Shared wrapper: `resources/views/components/common/<component-name>.blade.php`.
  - Always use `@props([...])` to declare default values and expected component properties.
- **Icons**:
  - Drop reusable SVGs in `resources/views/components/svg/<icon-name>.blade.php` and invoke via `<x-svg.icon-name />`.
  - For inline SVG icons in components, always use `fill-current` / `stroke-current` and size with `w-*` / `h-*` tokens.
- **Layout Structure**:
  - Dashboard pages: Wrapped in `@extends('layouts.app')` with `<x-common.page-breadcrumb>` at the top and content sections inside `<x-common.component-card>`.
  - Full-width / Auth pages: Wrapped in `@extends('layouts.fullscreen-layout')`.
- **State Management**:
  - Component-level state: Use `x-data="{ ... }"` in Alpine.js.
  - Global theme state: Managed via `Alpine.store('theme')` (handles `light`, `dark`, and system preference synced to `localStorage`).

## RTL Layout Architecture (Free Version Rules)

- **English Only (No Translations)**:
  - Translating the UI into other languages is a Pro-only feature. Free ships English only.
  - UI strings live in `lang/en.json` and are read with `__('key')`; there is no locale route or locale middleware.
  - The Language menu in the user dropdown looks the same as Pro, but picking a language only changes the layout direction (Arabic → RTL, all others → LTR). The text stays English.
- **RTL State & Toggle**:
  - Toggled via the Language menu in the user dropdown (`user-dropdown.blade.php`), backed by `Alpine.store("direction")` in `resources/js/app.js`.
  - Persisted in `localStorage.setItem("dir", "rtl" | "ltr")`.
  - Dynamically updates `document.documentElement.setAttribute("dir", ...)`.
  - Automatically closes the user dropdown after a language is selected.
  - Immediate execution script in `<head>` of `app.blade.php` and `fullscreen-layout.blade.php` prevents flash of unstyled LTR content.
- **CSS Logical Properties & RTL Spacing**:
  - **Margins & Paddings**: Use `ms-*` / `me-*`, `ps-*` / `pe-*` or explicit `ltr:ml-* rtl:mr-*` variants.
  - **Positioning**: Use `start-*` / `end-*` or `ltr:left-* rtl:right-*`.
  - **Borders**: Use `ltr:border-r rtl:border-l` or `border-s-*` / `border-e-*`.
  - **Border Radius**: Use `rounded-s-*` / `rounded-e-*`.
  - **Text Alignment**: Use `text-start` / `text-end` instead of `text-left` / `text-right`.
  - **Directional Glyphs**: Chevrons, back arrows, breadcrumb separators, and pagination arrows must flip in RTL using `rtl:rotate-180` or `rtl:-scale-x-100`.
  - **Off-Canvas Drawers**: Always scope mobile off-canvas drawer translation with `max-xl:-translate-x-full max-xl:rtl:translate-x-full` to prevent attribute specificity collision with `xl:translate-x-0` on desktop.

## Styling Rules

- Tailwind CSS **v4** — configured via `resources/css/app.css` using `@theme`. There is **no** `tailwind.config.js`.
- **Theme Tokens**: Always use predefined theme tokens:
  - Colors: `brand` (`50`–`950`), `gray` (`25`–`950`), `success`, `error`, `warning`, `blue-light`, `orange`, `theme-pink-500`, `theme-purple-500`.
  - Typography: `font-outfit`, `text-theme-xs/sm/xl`, `text-title-sm/md/lg/xl/2xl`.
  - Shadows: `shadow-theme-xs/sm/md/lg/xl`, `shadow-focus-ring`, `shadow-datepicker`, `shadow-tooltip`.
  - Z-Index: `z-1`, `z-9`, `z-99`, `z-999`, … `z-999999`.
- **Dark Mode**:
  - Dark mode is class-driven (`.dark` on `<html>`).
  - Every styled element must include its corresponding `dark:` variant (e.g. `bg-white dark:bg-gray-800 text-gray-800 dark:text-white/90 border-gray-200 dark:border-gray-800`).
- **Reusable Utility Classes**:
  - Check `resources/css/app.css` before writing custom styles (`custom-scrollbar`, `no-scrollbar`, `menu-item-*`, `menu-dropdown-*`, `input-placeholder-*`).
  - Third-party library overrides (ApexCharts, Flatpickr, FullCalendar, SimpleBar) are maintained at the bottom of `resources/css/app.css`.
- **No Hardcoded Hex**: Never hardcode hex colors directly in Blade `class=""` attributes. Use Tailwind theme tokens.

## Component & Interactive UI Rules

- **Single Responsibility**: Keep sub-components modular and focused (e.g. `MonthlySalesChart.blade.php`, `RecentOrders.blade.php`).
- **Alpine.js Event Handling**:
  - Do NOT attach redundant `@click` handlers to decorative spans inside `<label :for="...">` elements when `<input x-model="...">` is present, as this causes double-toggle event conflicts.
  - Use `x-cloak` alongside `[x-cloak] { display: none !important; }` to prevent flash of unstyled content during Alpine initialization.
- **Modals & Drawers**:
  - Implement using Alpine `x-data="{ isOpen: false }"` with `@keydown.escape.window="isOpen = false"`, overlay backdrop, and focus management.
- **Charts & Dataviz**:
  - Initialize ApexCharts instances in client-side scripts inside `resources/js/components/` or Alpine `x-init` hooks.
  - Synchronize chart theme colors dynamically on the `'theme-changed'` custom window event.

## Don'ts

- Don't install new Composer packages or NPM dependencies without asking the user.
- Don't hardcode physical directional utilities (`ml-*`, `mr-*`, `pl-*`, `pr-*`, `left-*`, `right-*`, `border-l-*`, `border-r-*`, `rounded-l-*`, `rounded-r-*`, `text-left`, `text-right`) without providing proper RTL support (`ltr:` / `rtl:` or CSS logical equivalents).
- Don't create a `tailwind.config.js` file — Tailwind v4 configuration belongs in `resources/css/app.css`.
- Don't hardcode user-facing strings in Blade files without adding corresponding keys to `lang/en.json`.
- Don't add extra locale files or real translations — that is a Pro feature.
- Don't edit files in `tailadmin-laravel-pro` or the other framework projects unless explicitly asked — focus changes on this Free codebase.
- Don't write inline CSS styles (`style="..."`) when Tailwind theme tokens and utility classes are available.

## Common Agent Workflows

### Add a new admin page

1. Add a `Route::get('/path', fn() => view('pages.category.page-name', ['title' => 'Page Title']))->name('route-name');` in `routes/web.php`.
2. Add metadata to `config/page-meta.php` for the new path.
3. Create `resources/views/pages/<category>/<page-name>.blade.php` extending `@extends('layouts.app')`.
4. Add page-specific components under `resources/views/components/main/<domain>/` and include with `<x-main.<domain>.<component> />`.
5. Add the page to `app/Helpers/MenuHelper.php` if it should appear in the sidebar.
6. Run `npm run build` to verify.

### Add a new Blade component

1. Create `.blade.php` under the appropriate `resources/views/components/<category>/` folder.
2. Declare props with `@props([...])` at the top.
3. Reference via `<x-<category>.<component-name> />` in pages or other components.

### Add/change a locale string

1. Edit `lang/en.json`.
2. Use the key in Blade: `__('key')` or `@lang('key')`.

### Modify a theme token or utility

1. Open `resources/css/app.css`.
2. Add/update tokens in the `@theme` block or `@utility` block.
3. Do not create `tailwind.config.js`.

## Verification Commands

- `npm run dev` — Vite dev server with HMR.
- `npm run build` — production build; must pass without errors.
- `composer run dev` — concurrent dev server (Laravel + Vite + Pail log viewer).
- `php artisan route:list` — list all registered routes.
