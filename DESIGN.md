# MNM Bookstore - Design System & Development Plan

## 1. Design System Tokens (Warm Humanist)
- **Colors:**
  - `brand-text`: #2D3748 (Charcoal)
  - `brand-muted`: #718096 (Cool Gray)
  - `brand-surface`: #F7FAFC (Light Gray)
  - `brand-accent`: #E27D60 (Terracotta)
  - `brand-accent-hover`: #C4674C
  - `brand-border`: #E2E8F0
- **Typography:**
  - Display Font: `Lora`, serif
  - Body Font: `Nunito`, sans-serif
- **Layout & CSS Defaults:**
  - Header: Sticky top, expands to `1440px` on top, shrinks to `max-w-7xl` with `backdrop-blur` and border radius on scroll. Mega menu for categories.
  - Buttons: Rounded `var(--radius-button)`, uses `shadow-soft`, on hover shifts up with `shadow-hover`.
  - Cards (Books): `aspect-[3/4]` for covers, CSS `object-cover`, fallback `onerror` handled in Blade.

## 2. Project State (August 28, 2026)
- **Database & Models:**
  - Migrated SQLite with tables: `categories`, `authors`, `books`.
  - Eloquent relations configured (`Book` belongsTo `Author` & `Category`).
- **Seeding:**
  - `DatabaseSeeder.php` is fully populated with 8 mock books (using Wikipedia Wikimedia images as stable placeholders).
- **Frontend / Client:**
  - `master.blade.php`: Configured Tailwind 4, custom CSS vars, Google Fonts.
  - `header.blade.php`: Scroll-shrinking header + Mega menu.
  - `home.blade.php`: Hero section, Category chips, Best Sellers grid, New Books grid. Eloquent integrated successfully.
  - *Note:* Always run `npm run build` after editing Blade files so Tailwind generates the new classes.

## 3. Next Steps (TODOs for next session)
- [ ] **Book Catalog Page (`/books`):**
  - Implement full grid list of all books.
  - Build Sidebar Filters (filter by Category, Author, Price range, Rating).
  - Implement Sorting (Newest, Price Low-High, Price High-Low).
- [ ] **Book Details Page (`/books/{slug}`):**
  - Show full cover, author details, synopsis, price, Add to Cart button, and Customer Reviews.
  - Related books section.
- [ ] **Cart & Checkout Logic:**
  - Session-based or Database-based shopping cart.
  - Checkout form and validation.
- [ ] **Auth & Customer Accounts:**
  - Login/Register pages.
  - Order history.

## 4. Coding Conventions
- **C³ Standard Enforced**: Must follow `/Volumes/Hybridd06/SkillAI/common-coding-conventions/README.md`.
- Controllers must use Verb-first methods (e.g., `showHomePage`, `listBooks`).
- Boolean vars must use prefixes: `is/has/can`.
