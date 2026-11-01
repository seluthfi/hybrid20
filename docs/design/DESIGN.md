---
name: Liquid Glass Athletic
colors:
  surface: '#fdf8ff'
  surface-dim: '#ddd7ed'
  surface-bright: '#fdf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f7f1ff'
  surface-container: '#f2ebff'
  surface-container-high: '#ece5fc'
  surface-container-highest: '#e6dff6'
  on-surface: '#1c1929'
  on-surface-variant: '#484555'
  inverse-surface: '#322e3f'
  inverse-on-surface: '#f4eeff'
  outline: '#797586'
  outline-variant: '#cac4d7'
  surface-tint: '#613ede'
  primary: '#613dde'
  on-primary: '#ffffff'
  primary-container: '#7a5af8'
  on-primary-container: '#ffffff'
  inverse-primary: '#cabeff'
  secondary: '#006b5c'
  on-secondary: '#ffffff'
  secondary-container: '#61f7db'
  on-secondary-container: '#007060'
  tertiary: '#9d3b60'
  on-tertiary: '#ffffff'
  tertiary-container: '#bc5379'
  on-tertiary-container: '#ffffff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e6deff'
  primary-fixed-dim: '#cabeff'
  on-primary-fixed: '#1d0061'
  on-primary-fixed-variant: '#491ac6'
  secondary-fixed: '#65fade'
  secondary-fixed-dim: '#40ddc2'
  on-secondary-fixed: '#00201b'
  on-secondary-fixed-variant: '#005045'
  tertiary-fixed: '#ffd9e2'
  tertiary-fixed-dim: '#ffb1c7'
  on-tertiary-fixed: '#3e001d'
  on-tertiary-fixed-variant: '#802349'
  background: '#fdf8ff'
  on-background: '#1c1929'
  surface-variant: '#e6dff6'
typography:
  display:
    fontFamily: Space Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
  display-mobile:
    fontFamily: Space Grotesk
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-lg:
    fontFamily: Space Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Space Grotesk
    fontSize: 26px
    fontWeight: '600'
    lineHeight: 34px
  headline-md:
    fontFamily: Space Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Space Grotesk
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  metric-tabular:
    fontFamily: Space Grotesk
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 32px
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  gutter: 1.25rem
  gutter-desktop: 2rem
  margin: 1rem
  margin-tablet: 2rem
  margin-desktop: 3rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.25rem
---

## Brand & Style

This design system embodies "Liquid Glass" for high-performance athletic lifestyle tracking. The aesthetic balances luminous optical refractions with calm, mindful discipline tailored for high-output hybrid training (caloric deficit/cutting, barbell strength progression, and 10K running splits). Rather than leaning into aggressive, dark neon gym tropes, the visual direction prioritizes ethereal clarity, optical depth, and serene empowerment.

The style blends advanced glassmorphic refraction, soft-tinted iridescent ambient caustics, and pill-shaped tactile geometry. The tone in voice and microcopy balances rigorous scientific sports metrics with an encouraging, grounded Indonesian cadence—warm, supportive, yet disciplined (*"Tenang, konsisten, selangkah demi selangkah"*). Key surfaces float with luminous 1px specular edges, deep background blurs (24px–40px), and smooth refraction gradients that evoke fluid water, light passing through crystal prisms, and effortless physical flow.

## Colors

The chromatic palette is anchored in translucent light refraction against soft, atmospheric canvas gradients. 

- **Primary Violet (`#7A5AF8`)**: The dominant energetic focal point, used for primary actions, current split progress indicators, and active workout tracking states.
- **Secondary Cyan (`#22CCB2`)**: Fresh aquatic luminescence used for cardiovascular/running metrics (pace, aerobic heart rate zone, and recovery stats).
- **Tertiary Iridescent Rose (`#EE7BA2`)**: Warm harmonic accent representing caloric burn, heart rate maximums, and subtle iridescent highlights across glass edges.
- **Neutral Deep Slate (`#1C1929`)**: High-contrast, optical legible typography tone to ensure crisp legibility on bright glass layers.
- **Substrate Backdrop**: Organic radial mesh blending neutral warm stone (`#E7E4EB`), soft atmospheric amethyst (`#DCD6F7`), and subtle twilight cyan (`#D2F1EC`).
- **Glass Shading Tiers**:
  - `glass-1` (Base Card): `rgba(255, 255, 255, 0.45)` with `1px solid rgba(255, 255, 255, 0.65)` border.
  - `glass-2` (Floating Controls/Inputs): `rgba(255, 255, 255, 0.70)` with `1px solid rgba(255, 255, 255, 0.85)` border.
  - `glass-3` (Hero Modal/Active Pill): `rgba(255, 255, 255, 0.90)` with `1px solid #FFFFFF` top/left specular bevel.

## Typography

Typography balances technical, metric-driven precision with human, supportive clarity.

- **Headline Font (`Space Grotesk`)**: Technical, athletic, and contemporary. Applied across performance KPIs, pace readouts, weights, repetitions, and section headlines. All numerical outputs must strictly enforce CSS `font-variant-numeric: tabular-nums` to prevent horizontal jitter during active stopwatch runs, interval sets, and macro calculations.
- **Body & Label Font (`Plus Jakarta Sans`)**: Gentle, humanistic geometric sans-serif that softens data density and delivers supportive coaching insights, Indonesian affirmations, and workout descriptions with optimal micro-legibility over translucent surfaces.
- **Hierarchical Rules**: Maintain minimum contrast against blurred backdrops; avoid thin font weights below 500 on lower glass tiers to prevent visual degradation.

## Layout & Spacing

The layout is built upon a fluid 12-column adaptive grid that allows multi-tiered glass sheets to float over an ambient background gradient.

- **Mobile (< 768px)**: 4-column structure with `margin: 1rem` and `gutter: 1.25rem`. Complex training split tables reflow into single-column glass cards with horizontal swipe capability for 10K running splits and exercise sets.
- **Tablet (768px - 1024px)**: 8-column layout with `margin: 2rem` and `gutter: 1.5rem`. Enables dual-split view (Daily Caloric & Macro Balance alongside Current Strength Routine).
- **Desktop (> 1024px)**: 12-column fixed-max layout (max content container width `1360px`) framed by `margin: 3rem` and `gutter-desktop: 2rem`. Metrics appear as interconnected glass modules with generous breathing room.
- **Component Breathing Space**: Liquid glass requires extended negative space around floating modules (`space-lg` to `space-xl`) to allow background light refractions and diffused drop shadows room to disperse without overlapping muddy contours.

## Elevation & Depth

Visual depth is achieved through an optical physics model of multi-layered refractive glass rather than conventional flat opaque shadows.

- **Tier 0 (Substrate)**: Atmospheric fluid mesh canvas with slowly undulating radial blurred hues of lavender, aqua, and soft porcelain.
- **Tier 1 (`glass-1` - Base Tray / Panels)**:
  - Background: `rgba(255, 255, 255, 0.38)`
  - Backdrop Filter: `blur(24px) saturate(180%)`
  - Border: `1px solid rgba(255, 255, 255, 0.55)`
  - Shadow: `0 12px 32px -4px rgba(31, 25, 54, 0.08), 0 4px 12px -2px rgba(31, 25, 54, 0.04)`
- **Tier 2 (`glass-2` - Content Cards & Controls)**:
  - Background: `rgba(255, 255, 255, 0.65)`
  - Backdrop Filter: `blur(32px) saturate(200%)`
  - Border: `1px solid rgba(255, 255, 255, 0.85)`
  - Top Bevel Light: Inset shadow `inset 0 1px 1px rgba(255, 255, 255, 0.95)`
  - Shadow: `0 20px 40px -8px rgba(31, 25, 54, 0.12), 0 8px 16px -4px rgba(31, 25, 54, 0.06)`
- **Tier 3 (`glass-3` - Floating Overlays & Action Pills)**:
  - Background: `rgba(255, 255, 255, 0.88)`
  - Backdrop Filter: `blur(40px) saturate(210%)`
  - Border: `1.5px solid #FFFFFF`
  - Caustic Glow: Dynamic ambient glow matching the active discipline—Violet `rgba(122, 90, 248, 0.25)` or Cyan `rgba(34, 204, 178, 0.25)` cast downward by 16px.

## Shapes

The shape system adopts a fluid, pill-driven geometric standard (`level 3`). Full curvatures convey fluid movement, speed, and tactile ergonomic comfort.

- **Buttons & Pills**: Completely rounded capsule geometry (`border-radius: 9999px`) across primary buttons, segmented tab toggles, tag chips, and micro-inputs.
- **Glass Panels & Cards**: Ultra-soft organic radii (`rounded-xl: 2rem` on desktop, `1.5rem` on mobile) evoking smooth water droplets or polished glass tiles.
- **Special Refractions**: Circular floating action hubs (32px - 56px diameters) for quick workout timers and instant metric logging, mimicking water lens beadings.

## Components

### Buttons
- **Primary Action (Liquid Violet Pill)**: Fully pill-shaped (`9999px`), gradient fill `linear-gradient(135deg, #8B6EFA 0%, #6842ED 100%)`, top specular reflection `inset 0 1.5px 1px rgba(255, 255, 255, 0.60)`, subtle bottom shadow `0 8px 20px rgba(104, 66, 237, 0.35)`. Text is pure white `Space Grotesk` medium.
- **Secondary Action (Liquid Cyan Pill)**: Gradient fill `linear-gradient(135deg, #3DE5CC 0%, #15B69F 100%)` with matching white specular highlight and cyan drop glow `0 8px 20px rgba(21, 182, 159, 0.30)`.
- **Tertiary Glass Button**: Translucent surface `rgba(255, 255, 255, 0.70)` with 1px border `rgba(255, 255, 255, 0.90)` and text `#1C1929`.

### Input Fields & Search Bars
- Translucent capsule background `rgba(255, 255, 255, 0.75)` with inner light depth `inset 0 1px 2px rgba(0, 0, 0, 0.04)`.
- High-contrast border: `1px solid rgba(255, 255, 255, 0.90)`. Focus state introduces a sharp `2px` luminous violet ring `rgba(122, 90, 248, 0.40)` with `blur(2px)`.
- Numeric input fields for workout logging enforce tabular numbers with quick-increment circular glass steppers (+ / -).

### Metric Cards & Containers
- Base tier `glass-1` enclosing nested `glass-2` sub-modules.
- Features top-right mini status chips (e.g., *"Defisit -350 kcal"*, *"Pace 5:12 /km"*, *"RPE 8.5"*).
- Card headers utilize `Space Grotesk` with tabular stats scaled to `metric-tabular`.

### Chips & Badges
- Compact capsules (`28px` height) with `rgba(255, 255, 255, 0.60)` background and 1px crisp border.
- Colored status dot (Cyan for Cardio/Running, Violet for Strength, Coral for Nutrition deficit).

### Checkboxes & Segmented Controls
- **Segmented Switch**: Pill-shaped glass container housing a sliding white pill button that translates smoothly with an elastic curve (`cubic-bezier(0.16, 1, 0.3, 1)`).
- **Checkboxes**: Circular soft-touch glass beads with an animated iridescent fill and crisp white checkmark upon completion of exercise sets.

### Hybrid Athletic Extensions
- **Running 10K Split Bar**: Segmented horizontal glass strip with progress filled via cyan liquid refraction, displaying kilometer pace intervals with fixed tabular spacing.
- **Strength Rep/Set Matrix**: Tier-2 glass list items featuring dynamic strike-through when marked complete, accented by celebratory subtle haptic micro-refractions.