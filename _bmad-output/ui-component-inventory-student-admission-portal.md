# UI Component Inventory - Student Admission Portal

## Overview
The application utilizes a hybrid approach:
- **Public/Landing:** Laravel Blade templates styled with TailwindCSS (via CDN and build process).
- **Dashboard/Portal:** Likely protected Blade views or a potential Single Page Application (SPA) mount point (implied by `resources/js/app.js` and Vite config, though source analysis showed minimal JS structure so far).

## Pages & Views
- **Landing Page (`welcome.blade.php`)**
  - **Navbar:** Responsive navigation with Login/Register links.
  - **Hero Section:** "Khởi đầu hành trình", CTA buttons.
  - **Stats:** Program count, support hours.
  - **Footer:** Links and copyright.
- **Dashboard (`dashboard.blade.php`)**
  - Protected area for students (implied existence).
- **Emails**
  - `application_result.blade.php`: Email template for admission results.

## Design System
- **Framework:** TailwindCSS (v4.0.0).
- **Font:** 'Instrument Sans'.
- **Theme:** Supports Dark Mode (`dark:bg-gray-900`).
- **Colors:** Primary Blue (`blue-600`), Gray scale.

## Assets
- **CSS:** `resources/css/app.css`
- **JS:** `resources/js/app.js`, `bootstrap.js` (Axios setup).
