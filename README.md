================================================================================
          GUIDELINES FOR AGENTS: LARAVEL + VITE ADAPTIVE WEB/MOBILE APP
================================================================================

1. OVERVIEW & ARCHITECTURE
--------------------------------------------------------------------------------
This project utilizes Laravel (Backend) + Vite (Bundler) with Inertia.js 
and React / Vue 3 + Tailwind CSS to deliver a seamlessly adaptive user experience:
  - Mobile Screens (< 768px): Renders as a Mobile App interface (Bottom Navigation Bar,
    compact cards, swipeable components, touch-optimized layouts).
  - Desktop Screens (>= 768px): Renders as a full Dashboard/Desktop interface 
    (Sidebar Navigation, top action bar, expanded data tables, multi-column layout).

Both layouts share the same Laravel backend routes, controllers, and state management
via Inertia.js, eliminating code duplication while tailoring UI/UX per screen size.


2. TECH STACK SPECIFICATIONS
--------------------------------------------------------------------------------
- Backend Framework : Laravel 11.x (or latest LTS)
- Build Tool        : Vite
- Adapter           : Inertia.js (Monolith SPA architecture)
- Frontend Framework: React (JSX/TSX) OR Vue 3 (Composition API / Script Setup)
- Styling           : Tailwind CSS v3+
- Icons             : Lucide React / Lucide Vue Next


3. KEY FEATURES & CAPABILITIES
--------------------------------------------------------------------------------
A. Adaptive Layout & Navigation:
   - Dynamic UI Switching: Automatic layout presentation shift based on screen width.
   - Mobile Bottom Navigation Bar: Fixed bottom bar for quick tab navigation on mobile devices.
   - Desktop Collapsible Sidebar: Persistent/collapsible sidebar for rich administrative navigation.
   - Touch-Optimized UX: Swipe actions, pull-to-refresh, bottom sheet drawers for mobile users.

B. Authentication & Role-Based Access Control (RBAC):
   - Secure Login / Logout with Inertia authentication state.
   - Multi-role permission checks (e.g., Admin, Manager, Operator) embedded in shared props.

C. Data Management & Visualization:
   - Adaptive Data Views: Automatic rendering of Data Tables on Desktop and Actionable Cards on Mobile.
   - Interactive Dashboards: Responsive charts (using Chart.js / Recharts / ApexCharts) scaled per viewport.
   - Real-time Filters & Search: Reactive debounced search and multi-parameter filters without full page reloads.

D. Performance & Progressive Web App (PWA) Ready:
   - SPA Navigation: Instant page transitions with Inertia.js (no page flashes).
   - Partial Reloads: Optimized data fetching (only request modified attributes).
   - PWA Support: Web App Manifest & Service Worker readiness for "Add to Home Screen" capability.


4. PROJECT DIRECTORY STRUCTURE
--------------------------------------------------------------------------------
resources/
├── js/
│   ├── Components/         # Reusable UI components (Buttons, Modals, Inputs)
│   │   ├── Mobile/         # Mobile-specific UI elements (BottomNav, BottomSheet, PullToRefresh)
│   │   └── Desktop/        # Desktop-specific UI elements (Sidebar, Header, DataTable)
│   ├── Layouts/            # Adaptive Layout Containers
│   │   ├── AppLayout.jsx   # Master layout with responsive conditional rendering
│   │   ├── MobileLayout.jsx
│   │   └── DesktopLayout.jsx
│   ├── Pages/              # Inertia Page Views (Dashboard, Users, Settings)
│   └── app.jsx             # Entry point for Vite & Inertia
└── css/
    └── app.css             # Tailwind CSS directives & custom utility styles


5. LAYOUT ADAPTATION STRATEGY
--------------------------------------------------------------------------------
Agents MUST adhere to the CSS-first responsive strategy using Tailwind CSS breakpoints:

A. Master Layout Wrapper (`AppLayout`):
   - Wrap application views in a master layout that includes both Mobile and Desktop 
     navigation wrappers, controlling visibility via Tailwind breakpoints (`hidden md:flex`, `block md:hidden`).
   - Ensures zero layout flash and smooth resizing transitions without requiring 
     server-side user-agent sniffing.

B. Navigation Structure:
   - Mobile Layout: Fixed Bottom Navigation Bar (`fixed bottom-0 inset-x-0 md:hidden z-50`).
   - Desktop Layout: Left Sidebar Navigation (`hidden md:flex md:w-64 md:flex-col fixed inset-y-0`).

C. Data Display Adaptation:
   - Mobile: Render lists as cards, collapsible accordions, or touch-friendly lists.
   - Desktop: Render lists inside fully featured data tables (`<table>`) with pagination and filter bars.


6. INERTIA.JS BEST PRACTICES FOR AGENTS
--------------------------------------------------------------------------------
1. Shared State:
   - Pass global user data, flash messages, and permissions via Inertia Middleware (`HandleInertiaRequests`).
   - Access shared data inside components using `usePage()` hook.

2. Partial Reloads & Lazy Loading:
   - Use `only` props on Inertia links/visits when changing tab views to preserve mobile state.
   - Implement `router.reload({ only: ['data'] })` for seamless pull-to-refresh on mobile views.

3. Form Handling:
   - Always use Inertia's `useForm` hook for validation error mapping and submission states.
   - Provide touch-friendly feedback (toast alerts / haptic-style micro-interactions).


7. TAILWIND CSS BREAKPOINT REFERENCE
--------------------------------------------------------------------------------
- Default (Mobile) : < 768px  -> Single column, bottom navigation, card-based.
- `md:` (Tablet)    : >= 768px -> Sidebar visible, table view enabled.
- `lg:` (Desktop)   : >= 1024px -> Multi-column grid, persistent panels.
- `xl:` (Large)     : >= 1280px -> Max content container width.


8. ENVIRONMENT & BUILD COMMANDS
--------------------------------------------------------------------------------
# Install Dependencies
composer install
npm install

# Local Development (Run both concurrently)
php artisan serve
npm run dev

# Production Build
npm run build
php artisan optimize

================================================================================
