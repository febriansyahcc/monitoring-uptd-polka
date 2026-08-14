---
name: frontend-design
description: Guidelines for building responsive, touch-optimized mobile (<768px) and desktop (>=768px) UI with Tailwind CSS.
---

# Adaptive Frontend Design System Guidelines

## 1. Responsive Breakpoints & Visibility
- **Mobile Viewport (<768px)**:
  - Bottom Navigation Bar: `fixed bottom-0 inset-x-0 md:hidden z-50`
  - Touch-friendly cards, accordions, and bottom sheets.
- **Desktop Viewport (>=768px)**:
  - Sidebar Navigation: `hidden md:flex md:w-64 md:flex-col fixed inset-y-0`
  - Expanded Data Tables (`<table>`) with pagination and search filters.

## 2. Touch & Accessibility UX
- Ensure min touch targets (44x44px) on mobile interfaces.
- Avoid content overlap with the bottom navigation bar by adding bottom padding `pb-16 md:pb-0`.
- Smooth transitions for sidebar collapse and modal drawers.
