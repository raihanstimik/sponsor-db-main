---
name: ICM Sponsor Enterprise Portal
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
  on-surface-variant: '#454650'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#767681'
  outline-variant: '#c6c5d1'
  surface-tint: '#505a98'
  primary: '#00094a'
  on-primary: '#ffffff'
  primary-container: '#18225e'
  on-primary-container: '#828bcd'
  inverse-primary: '#bbc3ff'
  secondary: '#954a00'
  on-secondary: '#ffffff'
  secondary-container: '#fd8a2a'
  on-secondary-container: '#632f00'
  tertiary: '#001626'
  on-tertiary: '#ffffff'
  tertiary-container: '#002b45'
  on-tertiary-container: '#2f96db'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dee0ff'
  primary-fixed-dim: '#bbc3ff'
  on-primary-fixed: '#081351'
  on-primary-fixed-variant: '#38427e'
  secondary-fixed: '#ffdcc6'
  secondary-fixed-dim: '#ffb785'
  on-secondary-fixed: '#301400'
  on-secondary-fixed-variant: '#713700'
  tertiary-fixed: '#cce5ff'
  tertiary-fixed-dim: '#93ccff'
  on-tertiary-fixed: '#001d31'
  on-tertiary-fixed-variant: '#004b73'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 44px
    letterSpacing: -0.02em
  display-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.015em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.04em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  sidebar-width: 260px
  header-height: 64px
---

## Brand & Style

The design system serves an enterprise B2B platform for managing large-scale national and international congresses, sponsor tiers, exhibit allocations, and compliance verification.

### Personality & Emotional Response
- **Authoritative & Reliable:** Built on structural precision, high data legibility, and corporate trust.
- **Dynamic Energy:** Energized by vibrant corporate orange accents reflecting active congress engagement and deal execution.
- **Controlled Administrative Clarity:** Reduces cognitive fatigue across multi-tab verification tables, complex approval states, and high-density financial metrics.

### Visual Style
The aesthetic aligns with **Corporate Modern Dashboard Architecture**:
- Clean slate backgrounds (`#F8FAFC`) with crisp, low-contrast structural borders (`#E2E8F0`).
- Deep corporate royal navy foundations (`#18225E`) for core navigational anchors and primary headers.
- Kinetic orange accents (`#EA7C1A`) reserved for key conversions, active tabs, and primary calls to action.
- Functional status indicators with distinct tint/border/text trios to make sponsor statuses (*Terverifikasi*, *Perlu Dicek*, *Tidak Aktif*) immediately scannable.

## Colors

The color palette directly stems from the official corporate identity: dominant deep royal navy blue coupled with energetic congress orange, complemented by clean slate utility neutrals and strict validation states.

### Core Swatches
- **Primary (`#18225E`):** Represents institutional authority, stability, and structure. Used for top/side navigation, heavy headings, and primary interactive states.
- **Secondary (`#EA7C1A`):** The signature congress accent. Applied to primary operational actions, active selection rings, and highlight badges.
- **Tertiary (`#0284C7`):** Corporate cyan/sky blue used for informational banners, technical document links, and secondary badges.
- **Neutral (`#64748B`):** Governs structural body typography, borders (`#E2E8F0`), muted labels (`#94A3B8`), and workspace canvas fills (`#F8FAFC`).

### Enterprise Status Colors
- **Success (*Terverifikasi*):** Surface `#ECFDF5`, Border `#A7F3D0`, Text `#065F46`, Indicator `#10B981`.
- **Warning / Review Needed (*Perlu Dicek*):** Surface `#FFFBEB`, Border `#FDE68A`, Text `#92400E`, Indicator `#F59E0B`.
- **Destructive / Flagged (*Tidak Aktif / Duplikat*):** Surface `#FFF1F2`, Border `#FECDD3`, Text `#9F1239`, Indicator `#F43F5E`.

## Typography

This system uses **Plus Jakarta Sans** uniformly across display, body, and tabular interfaces. Its geometric clarity and open counters offer legibility in high-density data tables and sponsor audit trails.

### Hierarchy & Application Guidelines
- **Display & Large Headlines:** Used for global overview metrics, congress titles, and high-level revenue figures. Use semi-bold to extra-bold with slight negative tracking (`-0.015em` to `-0.02em`).
- **Headlines (md/sm):** Reserved for card titles, modal headers, and table segment captions.
- **Body:** Set at 14px default for operational balance. Provides high readability without excessive vertical expansion.
- **Labels & Badges:** Use uppercase or medium tracking for status chips (*Terverifikasi*, *Review*, *Paket Platinum*). Numbers in tables should use tabular figures (`font-variant-numeric: tabular-nums`) to align financial sums and booth metrics.

## Layout & Spacing

The layout is built around a standard enterprise console architecture: a fixed-width collapsible sidebar (`260px`), a synchronized top control bar (`64px`), and a fluid main dashboard panel constrained to a maximum content width of `1600px`.

### Grid System
- **Desktop (>= 1280px):** 12-column fluid grid, 24px gutters, 32px outer canvas padding.
- **Tablet (768px - 1279px):** 8-column layout, 16px gutters, 20px padding. Sidebar shifts to icon-only rail or drawer overlay.
- **Mobile (< 768px):** 4-column flow, 12px gutters, 16px outer margin. Data tables switch to stacked swipe cards or horizontally scrollable containers with fixed header columns.

### Density Tiers
- **Table Density:** Compact padding (`8px 12px`) for sponsor lists, transaction logs, and booth allocations.
- **Form & Card Density:** Standard layout padding (`16px` to `24px`) for sponsor profile submission, document upload zones, and invoice generation flows.

## Elevation & Depth

Visual hierarchy uses **crisp planar boundaries** supported by subtle, cool-tinted drop shadows rather than heavy blurring.

### Elevation Levels
- **Level 0 (Flat Canvas):** `#F8FAFC`. Background canvas upon which cards sit.
- **Level 1 (Card & Module Surface):** `#FFFFFF` bordered with `1px solid #E2E8F0`. Shadow: `0 1px 3px 0 rgba(24, 34, 94, 0.04), 0 1px 2px -1px rgba(24, 34, 94, 0.04)`.
- **Level 2 (Dropdowns, Popovers & Hover Cards):** `#FFFFFF` with border `#CBD5E1`. Shadow: `0 4px 6px -1px rgba(24, 34, 94, 0.07), 0 2px 4px -2px rgba(24, 34, 94, 0.05)`.
- **Level 3 (Modals & Verification Drawers):** Shadow: `0 20px 25px -5px rgba(24, 34, 94, 0.12), 0 8px 10px -6px rgba(24, 34, 94, 0.08)`. Accompanied by a semi-transparent royal navy overlay: `rgba(24, 34, 94, 0.4)`.

## Shapes

The design system specifies **Soft** roundedness (`roundedness: 1`), conveying corporate discipline and crisp structural order:
- **Base inputs, buttons, chips, and small controls:** `4px` (`0.25rem`).
- **Cards, data tables, and modal dialogs:** `8px` (`0.5rem`).
- **Avatars, status pills, and counter dots:** `9999px` (Full circle/pill).

## Components

### Buttons
- **Primary Button:** Background corporate navy (`#18225E`), text `#FFFFFF`, 4px radius, hover state `#232F7A`. Used for global actions (e.g., "Simpan Sponsor", "Ekspor Laporan").
- **Accent Button:** Background corporate orange (`#EA7C1A`), text `#FFFFFF`, hover `#D96D12`. Used for primary stage transitions (e.g., "Verifikasi Sekarang", "Tambah Kontrak Baru").
- **Secondary / Outline Button:** Border `1px solid #CBD5E1`, background `#FFFFFF`, text `#18225E`, hover background `#F1F5F9`.
- **Destructive Button:** Tinted rose `#FFF1F2`, text `#9F1239`, border `1px solid #FECDD3`, hover background `#FFE4E6`.

### Status Badges & Chips
- Status badges use an inline status dot (6px) with medium label typography:
  - **Terverifikasi:** Background `#ECFDF5`, text `#065F46`, border `#A7F3D0`.
  - **Perlu Dicek:** Background `#FFFBEB`, text `#92400E`, border `#FDE68A`.
  - **Tidak Aktif / Duplikat:** Background `#FFF1F2`, text `#9F1239`, border `#FECDD3`.
  - **Sponsor Tier Badges:** Platinum (Deep Navy + White text), Gold (Warm Amber tone), Silver (Cool Slate tone).

### Form Controls & Inputs
- **Text Inputs:** Height `40px`, background `#FFFFFF`, border `1px solid #CBD5E1`, text `#0F172A`, placeholder `#94A3B8`. On focus: border `#EA7C1A`, box-shadow `0 0 0 3px rgba(234, 124, 26, 0.15)`.
- **Checkboxes & Radios:** Checked fill `#18225E` or `#EA7C1A`, border radius `3px` for checkboxes and `50%` for radios.

### Data Tables
- **Header:** Background `#F8FAFC`, bottom border `2px solid #E2E8F0`, uppercase 11px label font `#64748B`.
- **Row:** Height `52px`, alternating hover background `#F8FAFC`, bottom border `1px solid #F1F5F9`.
- **Cell Content:** Tabular numeric alignment for financial commitments and booth IDs.

### Cards & Summary KPI Widgets
- Pure white container with `1px solid #E2E8F0` border and 8px corner radius.
- KPI metric display features a small orange accent indicator bar (3px width) on the card's left boundary to establish visual energy.