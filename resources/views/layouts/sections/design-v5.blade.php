{{--
  ════════════════════════════════════════════════════════════════════
  Travel Orbit MIS — Design System v5
  MINIMAL BASE  (neomorphism stays a scoped dashboard accent)

  WHY THIS IS A BLADE PARTIAL AND NOT resources/css/app.css
  ---------------------------------------------------------
  app.css is compiled by Vite into public/build with a hashed filename.
  That build is currently STALE: public/build was compiled 2026-07-15
  16:19, app.css was edited 17:05, and the edit multiplied every
  font-size by 1.2 to compensate for --ui-zoom:0.82. Editing app.css
  would therefore require `npm run build`, which would also ship that
  pending ~20% font-size increase. Fonts are explicitly not to change,
  so this layer ships as an inline <style> in the layout instead:
  it renders after the compiled <link>, wins on cascade order, needs no
  build, and touches no compiled asset. There is deliberately NOT a
  single font-size declaration below.

  WHAT THIS DOES
  --------------
  app.css v4 is glassmorphism: translucent fills, 16-24px backdrop-blur,
  20px radii, gradient buttons with coloured glow. On a data-dense ops
  screen that costs contrast where figures must be read fast. v5
  flattens the base — solid surfaces, visible hairlines, restrained
  shadow, no blur, no gradient fills.

  NO NEOMORPHISM ANYWHERE — ONE FLAT SURFACE
  ------------------------------------------
  Two earlier attempts failed and are recorded here so they are not
  retried. (1) Defining .neo-raised/.neo-inset/.neo-chip globally: dead
  code, because the pages using those names define them in their own
  page-level <style>, which renders in the BODY and beats any
  layout-level rule. (2) Keeping the dashboards' #EAEEF3/#C4CBD6
  neomorphic grounds while flattening everything else: that left grey
  slabs sitting on a white page as the loudest thing on screen, so the
  flattening read as "nothing changed".

  Note also that v4's glassmorphism was largely theoretical: translucent
  white (alpha .75-.9) over a #F8FAFC page composites to ~#FDFEFE, and
  the 16px blur had nothing behind it to blur. Removing it changes
  almost nothing on its own — the visible win is that cards now have a
  real #E2E8F0 edge instead of an invisible white one.

  So: one flat white card language everywhere, and the neo-* class names
  that survive in dashboard.blade.php, selling-board.blade.php and
  departure-arrival-report.blade.php are historical only — those rules
  are now flat and carry a comment saying so.

  OUT OF SCOPE (deliberately untouched)
  -------------------------------------
  Font sizes and --ui-zoom · the dark sidebar (#layout-menu/.sb-*/.menu-*)
  · Call Desk (own stylesheet + layout) · the .to-select-*/.to-date-*
  widget kits · inline style="" attributes on booking-show /
  create-booking (464 each — those need deliberate per-page work, not a
  blind sweep).

  TO REVERT: delete the @include in layouts/sections/styles.blade.php.
  ════════════════════════════════════════════════════════════════════
--}}
<style>
/* ── Page ground ──────────────────────────────────────────────────
   v4 paints .layout-wrapper/.layout-page/.content-wrapper with
   `background: var(--to-page) !important`, so it already owns the
   visible ground and body's three radial washes never actually show
   in the content area. Nothing to do here — noted so nobody "fixes"
   the body gradient later expecting a visual change. */

/* ── Cards: the workhorse surface ─────────────────────────────── */
.card {
  border-radius: 14px;
  border: 1px solid var(--to-border);
  background: #FFFFFF;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  box-shadow: 0 1px 2px rgba(15,23,42,0.04);
  transition: box-shadow .12s ease, border-color .12s ease;
}
/* v4 lifted cards on hover (translateY + indigo glow). On list pages
   that makes a whole table twitch under the cursor, so hover is
   reduced to a shadow cue with no movement. Border-color is left
   alone so .card.accent-* keeps its coloured edge. */
.card:hover {
  transform: none;
  box-shadow: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
}
/* v4 pins a 20px !important top radius sized for its old 20px card. */
.card-header {
  background: transparent;
  border-bottom: 1px solid var(--to-border);
  border-radius: 14px 14px 0 0 !important;
}
.card-footer {
  background: transparent;
  border-top: 1px solid var(--to-border);
}

/* ── Buttons ───────────────────────────────────────────────────────
   Ordered by real usage in resources/views. Every variant gets an
   explicit :hover, because v4's hover rules carry gradient + coloured
   glow + translateY and would otherwise snap back on mouseover —
   v4's own `.btn:hover{transform:none}` does not save us, it has the
   same specificity as `.btn-orange:hover` but comes earlier. */
.btn { border-radius: 10px; box-shadow: none; }

.btn-primary            { background: var(--to-indigo); color: #fff; box-shadow: none; }
.btn-primary:hover      { background: #4338CA; color: #fff; box-shadow: none; transform: none; }

/* Amber needs dark ink: #fff on #F59E0B is ~2.1:1 and fails AA.
   v4 already had this right — keep it. */
.btn-orange             { background: var(--to-amber); color: #0F172A; box-shadow: none; }
.btn-orange:hover       { background: #D97706; color: #0F172A; box-shadow: none; transform: none; }

.btn-icon               { background: var(--to-indigo-dim); border: 1px solid rgba(79,70,229,0.20); color: var(--to-indigo); border-radius: 10px; box-shadow: none; }
.btn-icon:hover         { background: var(--to-indigo); color: #fff; box-shadow: none; transform: none; }

.btn-outline-primary        { background: transparent; border: 1px solid rgba(79,70,229,0.35); color: var(--to-indigo); box-shadow: none; }
.btn-outline-primary:hover  { background: var(--to-indigo-dim); color: var(--to-indigo); box-shadow: none; transform: none; }

.btn-outline-danger:hover   { background: var(--to-rose-dim); color: var(--to-rose); border-color: rgba(244,63,94,0.35); box-shadow: none; transform: none; }
.btn-outline-success:hover  { background: var(--to-emerald-dim); color: #047857; border-color: rgba(16,185,129,0.35); box-shadow: none; transform: none; }
.btn-outline-secondary      { background: var(--to-subtle); border: 1px solid var(--to-border); color: var(--to-slate); box-shadow: none; }
.btn-outline-secondary:hover{ background: #E8EDF3; color: var(--to-slate); border-color: #CBD5E1; box-shadow: none; transform: none; }

/* Currently unused in views, but defined so they are accessible the
   day someone reaches for them (white on the 500-weight tokens fails AA). */
.btn-success       { background: #047857; color: #fff; box-shadow: none; }
.btn-success:hover { background: #036B4F; color: #fff; box-shadow: none; transform: none; }
.btn-danger        { background: #BE123C; color: #fff; box-shadow: none; }
.btn-danger:hover  { background: #A20D33; color: #fff; box-shadow: none; transform: none; }

/* ── Tables: maximum legibility, minimum ornament ─────────────────
   The header keeps a deliberately heavier rule than the row dividers.
   Dropping both v4 separation cues at once (indigo tint AND 2px rule)
   left the header visually continuous with row 1 on wide grids. */
.table thead th {
  background: transparent;
  border-bottom: 2px solid #CBD5E1;
  color: var(--to-slate-dim);
}
.table tbody td { border-bottom: 1px solid var(--to-border); }
.table tbody tr:last-child td { border-bottom: none; }
/* Matches v4's selector exactly (.table, not .table-hover): ~4 tables
   in the app omit .table-hover and would otherwise keep v4's indigo
   gradient hover while the rest went flat. */
.table tbody tr:hover td { background: rgba(79,70,229,0.045); }

/* ── Form controls ────────────────────────────────────────────────
   #E2E8F0 on white is 1.23:1 and fails WCAG 1.4.11 (3:1 for UI
   component boundaries) — and these fields sit on white cards and a
   now-white filter bar, so the fill carries no boundary either.
   #CBD5E1 is 3.05:1 and passes. */
.form-control, .form-select {
  border-radius: 10px;
  border: 1px solid #CBD5E1;
  background: #FFFFFF;
  box-shadow: none;
}
.form-control:hover, .form-select:hover { border-color: #94A3B8; box-shadow: none; }
.form-control:focus, .form-select:focus {
  border-color: var(--to-indigo);
  box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
  background: #FFFFFF;
}
/* Addons kept in step with the flattened control they sit against. */
.input-group-text {
  background: var(--to-subtle);
  border: 1px solid #CBD5E1;
  color: var(--to-slate-dim);
  box-shadow: none;
}
/* The navbar search sits on a white bar with border-0 shadow-none in
   the markup, which would leave it with no affordance at all. */
.search-input, .search-input-wrapper { background: var(--to-subtle); }

/* ── Modals ───────────────────────────────────────────────────────
   Header and footer are flattened too — otherwise a flat white body
   sits sandwiched between two indigo-tinted bands. */
.modal-content {
  border-radius: 16px;
  border: 1px solid var(--to-border);
  background: #FFFFFF;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  box-shadow: 0 16px 48px rgba(15,23,42,0.16);
}
.modal-header { background: transparent; border-bottom: 1px solid var(--to-border); }
.modal-footer { background: transparent; border-top: 1px solid var(--to-border); }

/* ── Pagination ───────────────────────────────────────────────────
   v4 pins `border-radius:10px !important` on .page-link, and its
   `.page-item.active .page-link` (0,3,0) carries an indigo glow that a
   plain `.page-link{box-shadow:none}` (0,2,0) cannot reach. */
.page-link {
  border: 1px solid var(--to-border);
  border-radius: 10px !important;
  color: var(--to-slate);
  background: #FFFFFF;
  box-shadow: none;
}
.page-link:hover { background: var(--to-subtle); color: var(--to-slate); box-shadow: none; }
.page-item.active .page-link {
  background: var(--to-indigo);
  border-color: var(--to-indigo);
  color: #fff;
  box-shadow: none;
}

/* ── Page furniture ───────────────────────────────────────────────
   .to-stat is a report KPI tile. It stays FLAT (not neomorphic): it is
   a primary, repeated surface, and on #F8FAFC the neo highlight is
   invisible anyway. Its v4 ::after accent stripe is left intact and is
   what distinguishes one tile from the next. */
.to-stat {
  background: #FFFFFF;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  border: 1px solid var(--to-border);
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(15,23,42,0.04);
  transition: box-shadow .12s ease;
}
.to-stat:hover {
  transform: none;
  box-shadow: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
}
.to-filter-bar {
  background: #FFFFFF;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  border: 1px solid var(--to-border);
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(15,23,42,0.04);
}
.to-navbar {
  background: #FFFFFF !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
  border: 1px solid var(--to-border) !important;
  border-radius: 14px !important;
  box-shadow: 0 1px 2px rgba(15,23,42,0.04) !important;
}

/* ── Focus visibility ─────────────────────────────────────────────
   One consistent ring for anything keyboard-reachable that does not
   already define its own. */
.btn:focus-visible, .page-link:focus-visible, .to-nav-btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
}

@media (prefers-reduced-motion: reduce) {
  .card, .to-stat, .btn { transition: none; }
}

/* ── Sidebar user photo ───────────────────────────────────────────
   The 32px avatar in app.css was too small to actually recognise a
   face. Overridden here rather than at source: app.css only reaches
   the browser through a Vite rebuild, which would also ship unrelated
   font-size changes. The size below is the avatar box only — it does
   not alter any text in the app. */
.sb-uc-avatar { width: 44px !important; height: 44px !important; border-radius: 12px !important; }
.sb-uc-initials { font-size: 1.02rem; }
</style>
