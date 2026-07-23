---
name: Hydro-Tech Kinetic
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#434656'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#737688'
  outline-variant: '#c3c5d9'
  surface-tint: '#004ced'
  primary: '#003ec7'
  on-primary: '#ffffff'
  primary-container: '#0052ff'
  on-primary-container: '#dfe3ff'
  inverse-primary: '#b7c4ff'
  secondary: '#545f73'
  on-secondary: '#ffffff'
  secondary-container: '#d5e0f8'
  on-secondary-container: '#586377'
  tertiary: '#863500'
  on-tertiary: '#ffffff'
  tertiary-container: '#ad4600'
  on-tertiary-container: '#ffded0'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dde1ff'
  primary-fixed-dim: '#b7c4ff'
  on-primary-fixed: '#001452'
  on-primary-fixed-variant: '#0038b6'
  secondary-fixed: '#d8e3fb'
  secondary-fixed-dim: '#bcc7de'
  on-secondary-fixed: '#111c2d'
  on-secondary-fixed-variant: '#3c475a'
  tertiary-fixed: '#ffdbcc'
  tertiary-fixed-dim: '#ffb693'
  on-tertiary-fixed: '#351000'
  on-tertiary-fixed-variant: '#7a3000'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
  success-green: '#10B981'
  warning-yellow: '#FBBF24'
  error-red: '#EF4444'
  surface-bg: '#F8FAFC'
  action-yellow: '#FFD600'
typography:
  display-lg:
    fontFamily: Montserrat
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Montserrat
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Montserrat
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  code-booking:
    fontFamily: JetBrains Mono
    fontSize: 16px
    fontWeight: '500'
    lineHeight: 24px
  stat-value:
    fontFamily: Montserrat
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  margin-mobile: 16px
  margin-desktop: 32px
  gutter-grid: 20px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style

The design system is built on the narrative of **Hydro-Tech Kinetic**: a fusion of automotive precision and high-speed digital efficiency. The brand personality is energetic, professional, and transparent, aimed at tech-savvy vehicle owners and high-volume service operators.

The visual style is **Corporate / Modern** with a **High-Tech** edge. It utilizes a crisp, structured layout to convey trustworthiness, while energetic accents and subtle depth suggest speed and innovation. The interface prioritizes "at-a-glance" readability to facilitate booking under 2 minutes and real-time status monitoring.

Key characteristics include:
- **Kinetic Energy:** Use of diagonal accents and directional iconography to imply movement.
- **Digital Transparency:** Heavy use of status indicators and data-rich cards to keep the user informed.
- **Industrial Precision:** A systematic approach to spacing and alignment that mirrors the organized nature of a professional wash bay.

## Colors

The palette is anchored by **Vibrant Blue**, symbolizing water, technology, and reliability. This is paired with a deep charcoal secondary color for professional contrast.

**Tertiary and Named Colors:**
- **Safety Orange (#FF6B00):** Used exclusively for high-priority CTAs and "In Progress" states.
- **Action Yellow (#FFD600):** Used for secondary CTAs and "Hampir Penuh" (Almost Full) status warnings.
- **Semantic Logic:** Green, Yellow, and Red are strictly reserved for availability (Available/Almost Full/Full) and status updates (Success/Pending/Error) to maintain cognitive clarity.

**Color Mode:** The default experience is Light, utilizing a soft gray background (`#F8FAFC`) to reduce glare on mobile devices and improve legibility in outdoor environments (wash bays).

## Typography

This design system uses a dual-font strategy to balance impact with utility.

- **Headlines (Montserrat):** Geometric and bold, used for page titles and high-level marketing copy to project energy and confidence.
- **Body & UI (Inter):** Highly legible and neutral, used for all functional text, inputs, and descriptions to ensure clarity across devices.
- **Specialized (JetBrains Mono):** Reserved specifically for booking codes, vehicle license plates, and technical ID strings to prevent character confusion (e.g., '0' vs 'O').

**Scale & Behavior:** 
On mobile devices, headlines scale down to prevent excessive wrapping. Labels use uppercase styling with increased letter spacing to distinguish them from body copy in dense dashboard views.

## Layout & Spacing

The design system employs a **Fluid-Fixed Hybrid** layout model. 

- **Customer Mobile:** A single-column fluid layout with 16px side margins. Service selection uses a 2-column grid for card density.
- **Admin/Kasir Web:** A 12-column grid with a fixed sidebar (280px). Content areas use a flexible grid to accommodate different monitor sizes while maintaining a maximum content width of 1440px.

**Spacing Rhythm:** All spacing is derived from a 4px base unit. 
- Use `stack-md` (16px) for standard padding within cards and between form fields.
- Use `stack-lg` (32px) to separate major sections or distinct visual groups in the dashboard.
- The **Kanban Board** for operators uses a condensed horizontal gutter (12px) to maximize the number of visible columns on tablet screens.

## Elevation & Depth

Visual hierarchy is managed through **Tonal Layers** and **Ambient Shadows** to create a structured workspace.

- **Base Layer:** The surface background (`#F8FAFC`) is the lowest level.
- **Container Level:** Cards and dashboard widgets sit on the base layer with a subtle white background and a "Low-Contrast" border (`1px solid #E2E8F0`).
- **Elevated Level:** Floating actions (like "Add Booking" or "Scan QR") and Active Cards use an ambient shadow: `0px 4px 12px rgba(0, 82, 255, 0.08)`. The shadow is tinted with the primary blue to maintain the high-tech, clean aesthetic.
- **Overlays:** Modals and bottom sheets use a backdrop blur (8px) and a higher elevation shadow to pull the user's focus during critical tasks like payment confirmation.

## Shapes

The shape language is **Rounded**, striking a balance between friendly approachability and mechanical precision.

- **Standard Elements:** Input fields, service cards, and small buttons use a 0.5rem (8px) radius.
- **Large Components:** Onboarding containers and dashboard widgets use a 1rem (16px) radius to soften the high-density information layout.
- **Interactive Pills:** Status badges (e.g., "Washing") and the primary "Book Now" buttons on mobile utilize the `rounded-xl` (1.5rem) or full pill-shape to indicate high interactivity and provide a modern feel.
- **Photos:** The "Before/After" image containers use the standard 0.5rem radius to maintain consistency with UI cards.

## Components

### Buttons
- **Primary:** High-contrast blue with white text. Full-width on mobile for thumb accessibility.
- **CTA (Orange/Yellow):** Reserved for "Pay Now" or "Start Wash" to create high visual urgency.
- **Ghost:** Used for "Add Note" or "Back" actions to keep the hierarchy clean.

### Cards & Slots
- **Time Slots:** Square-ish cards with a centered time label. State changes are indicated by border color and background fill (e.g., Blue border for selected, Gray background for full).
- **Service Cards:** Feature a small icon or image, title in `headline-md`, and price in the accent color.

### Inputs & Forms
- **License Plate Input:** Uses `code-booking` font, uppercase transformation, and high contrast for error-free entry.
- **Progress Timeline:** A vertical stepper with colored dots. The current step pulses slightly to indicate real-time tracking.

### Admin Dashboard Specifics
- **Stat Cards:** Large `stat-value` numbers with a small trend indicator (percentage).
- **Kanban Cards:** Condensed cards featuring the vehicle type icon, time remaining, and a quick-action "Next Status" button.
- **Badges:** Small, high-contrast pills for status. Text is always uppercase `label-caps`.