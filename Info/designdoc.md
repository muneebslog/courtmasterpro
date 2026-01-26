# Design Document
## Badminton Tournament Management System (BTMS)

---

## 1. Design Goals

- Professional, modern SaaS-style UI
- Extremely clear for officials under pressure
- Fast readability during live matches
- Minimal clicks for common actions
- Consistent layouts across all pages
- Scalable for future features

---

## 2. Design Inspiration

Primary inspiration:
- ProTasks – Task Manager SaaS (Dribbble)

Key visual traits:
- Card-based layout
- Clean typography
- Soft borders and shadows
- Subtle color usage
- Clear hierarchy

---

## 3. Color Palette

### Primary Colors
- **Primary Blue**: #2563EB (Actions, links, highlights)
- **Primary Green**: #16A34A (Win, success, live status)
- **Primary Red**: #DC2626 (Red card, errors, walkover)

### Neutral Colors
- **Background**: #F8FAFC
- **Card Background**: #FFFFFF
- **Border**: #E5E7EB
- **Muted Text**: #6B7280
- **Primary Text**: #111827

### Status Colors
- Yellow Card: #FACC15
- Injury: #FB923C
- Live Match Indicator: #22C55E

---

## 4. Typography

- **Primary Font**: Inter (or system sans-serif fallback)
- **Headings**: Semi-bold
- **Body Text**: Regular
- **Numbers / Scores**: Medium / Semi-bold

Font Scale:
- Page Title: 24–28px
- Section Title: 18–20px
- Body Text: 14–16px
- Small Meta Text: 12px

---

## 5. Layout System

### Global Layout
- Left sidebar navigation
- Top header with tournament selector
- Main content area with cards and tables

### Grid
- 12-column responsive grid
- Max-width container for large screens
- Stack cards vertically on smaller screens

---

## 6. Core UI Components

### 6.1 Sidebar
- Logo at top
- Navigation items:
  - Dashboard
  - Tournaments
  - Events
  - Matches
  - Live Scores
  - Results
- Active state highlighted with primary color

---

### 6.2 Header Bar
- Current Tournament Name
- Event Selector (dropdown)
- User profile / logout

---

### 6.3 Cards

Used for:
- Stats
- Match summaries
- Scoreboards

Card style:
- Rounded corners (12px)
- Soft shadow
- Clear title
- Action button on top-right if needed

---

### 6.4 Tables

Used for:
- Player lists
- Match lists
- Events

Table features:
- Sticky header
- Zebra rows or subtle separators
- Status badges
- Action buttons on right

---

## 7. Key Screens Design

### 7.1 Dashboard
Cards:
- Total Tournaments
- Live Matches
- Matches Today
- Completed Matches

Below cards:
- Live Matches Table
- Upcoming Matches

---

### 7.2 Tournament Creation
- Step-based form
- Simple inputs
- Clear CTA button
- No clutter

---

### 7.3 Event Management
- Event cards (U16, U19, etc.)
- Badge for Individual / Team
- Button: “Create Matches”

---

### 7.4 Match View (Umpire Screen)

Layout:
- Left: Match details & players
- Center: Large score display
- Right: Controls

Controls:
- Increment / decrement score
- Set winner
- Yellow / Red card buttons
- Injury / Walkover buttons

Focus:
- Zero distraction
- Big numbers
- Fast interaction

---

### 7.5 Live Score Public View

- Dark-on-light or optional dark mode
- Large readable scores
- Match status indicator
- Auto-refresh

No admin controls visible.

---

### 7.6 Team Match View

- Vertical list of sets
- Each set shows:
  - Type (Singles / Doubles)
  - Players
  - Score
  - Status
- Clear indication of match progress

---

## 8. UI States

### Loading
- Skeleton loaders (not spinners)

### Empty State
- Icon + short message
- CTA button

### Error State
- Inline messages
- Clear recovery action

---

## 9. Interaction Rules

- One primary action per screen
- Destructive actions require confirmation
- Live actions provide instant feedback
- Keyboard-friendly where possible

---

## 10. Responsiveness

- Desktop-first
- Tablet optimized (umpire use)
- Mobile: view-only (live scores)

---

## 11. Accessibility

- High contrast text
- Large tap targets
- Clear focus states
- Color never used alone to convey meaning

---

## 12. Design Principles

- Clarity over beauty
- Speed over animations
- Consistency over creativity
- Minimal but powerful

---

## 13. Handoff Notes

- Components should be reusable
- Design tokens for colors & spacing
- No page-specific hacks
- AI-generated UI must follow this doc strictly

---
