---
name: laravel-inertia-adaptive
description: Practices for building monolithic SPAs using Laravel, Inertia.js, and Vue/React with shared state and adaptive layouts.
---

# Laravel + Inertia.js Adaptive Architecture

## 1. Directory Structure
```
resources/
├── js/
│   ├── Components/
│   │   ├── Mobile/    # BottomNav, BottomSheet, PullToRefresh
│   │   └── Desktop/   # Sidebar, Header, DataTable
│   ├── Layouts/       # AppLayout, MobileLayout, DesktopLayout
│   ├── Pages/         # Inertia Views
│   └── app.jsx / app.js
```

## 2. Inertia Best Practices
- **Shared Props**: Pass global user auth & RBAC permissions via `HandleInertiaRequests` middleware.
- **Partial Reloads**: Use `router.reload({ only: ['data'] })` for pull-to-refresh & tab switches.
- **Forms**: Always use Inertia `useForm` hook for form state & validation mapping.
