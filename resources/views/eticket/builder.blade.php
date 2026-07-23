<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>E-Ticket Builder | Travel Orbit UK</title>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

  :root{
    /* Softer, lighter take on the Travel Orbit palette — same indigo/magenta/
       orange family as the logo, pulled down in saturation so the ticket reads
       calm and airy rather than bold/loud. */
    --ink:#2b3040; --sub:#6b7280; --faint:#a3a9b7;
    --line:#ebedf3; --line2:#f2f3f8; --mist:#f9fafc; --paper:#ffffff;
    --indigo:#5b56d6; --indigo-dk:#4640b8;
    --magenta:#dd6ba3; --orange:#f2a15c;
    --green:#3fa168; --green-bg:rgba(63,161,104,.10);
  }
  *{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#eef0f5;color:var(--ink);
    font-family:'Inter','Segoe UI',Arial,sans-serif;-webkit-font-smoothing:antialiased}
  [x-cloak]{display:none !important}

  /* ================= SHELL ================= */
  .shell{display:grid;grid-template-columns:420px 1fr;min-height:100vh}
  .panel{padding:22px;overflow-y:auto;max-height:100vh}
  .left{background:#fff;border-right:1px solid var(--line)}
  .right{background:#e4e6ee;display:flex;flex-direction:column;align-items:center}

  .back-link{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;color:var(--sub);
    text-decoration:none;margin-bottom:14px}
  .back-link:hover{color:var(--indigo)}

  .brandbar{display:flex;align-items:center;gap:10px;margin-bottom:18px}
  .brandbar img{height:30px}
  .brandbar .t{font-family:'Manrope';font-weight:800;font-size:13px;color:var(--ink)}
  .brandbar .t small{display:block;font-weight:600;font-size:9.5px;color:var(--faint);letter-spacing:.4px;text-transform:uppercase}

  .fieldset{border:1px solid var(--line);border-radius:8px;padding:14px;margin-bottom:14px;background:var(--mist)}
  .fieldset h3{font-family:'Manrope';font-size:10.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
    color:var(--indigo);margin:0 0 10px;display:flex;align-items:center;gap:7px}
  .fieldset h3 svg{width:13px;height:13px;flex-shrink:0}
  .fieldset h3 .cnt{margin-left:auto;font-size:9.5px;color:var(--faint);font-weight:700;text-transform:none;letter-spacing:0}
  .frow{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
  .frow.c3{grid-template-columns:1fr 1fr 1fr}
  .frow.c1{grid-template-columns:1fr}
  .field label{display:block;font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--faint);margin-bottom:3px}
  .field input,.field select,.field textarea{width:100%;border:1px solid var(--line);border-radius:7px;padding:6px 8px;font:inherit;
    font-size:12.5px;background:#fff;color:var(--ink)}
  .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 2px rgba(51,46,143,.12)}
  .field textarea{resize:vertical;min-height:52px;line-height:1.4}

  .pax-card{border:1px solid var(--line);border-radius:8px;padding:10px;margin-bottom:10px;background:#fff;position:relative}
  .card-remove{position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:6px;border:none;
    background:rgba(220,38,38,.08);color:#dc2626;cursor:pointer;font-size:12px;line-height:1}
  .add-btn{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;border:1.5px dashed var(--indigo);
    border-radius:8px;background:transparent;color:var(--indigo);font-weight:700;font-size:12px;padding:8px;cursor:pointer}
  .add-btn:hover{background:rgba(51,46,143,.05)}

  /* booking search */
  .search-wrap{position:relative}
  .search-results{position:absolute;left:0;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid var(--line);
    border-radius:8px;box-shadow:0 12px 28px rgba(60,50,120,.14);max-height:260px;overflow-y:auto;z-index:20}
  .search-results button{display:block;width:100%;text-align:left;padding:8px 12px;border:none;background:none;
    font-size:12px;color:var(--ink);cursor:pointer;border-bottom:1px solid var(--line2)}
  .search-results button:hover{background:var(--mist)}
  .search-results .empty{padding:10px 12px;font-size:11.5px;color:var(--faint);font-style:italic}
  .loading-tag{font-size:10.5px;color:var(--indigo);font-weight:700;margin-top:6px}

  /* selected-booking chip (replaces the search box once a booking is picked) */
  .selected-booking{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;
    background:rgba(32,8,160,.06);border:1px solid rgba(32,8,160,.22)}
  .selected-booking .sb-icon{flex-shrink:0;width:30px;height:30px;border-radius:6px;display:flex;align-items:center;
    justify-content:center;background:var(--indigo);color:#fff}
  .selected-booking .sb-icon svg{width:15px;height:15px}
  .selected-booking .sb-info{min-width:0;flex:1}
  .selected-booking .sb-ref{font-family:'Manrope';font-weight:800;font-size:13px;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .selected-booking .sb-name{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--sub);font-weight:600;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .selected-booking .sb-name svg{width:10px;height:10px;color:var(--faint);flex-shrink:0}
  .selected-booking .sb-change{flex-shrink:0;border:1px solid var(--indigo);background:#fff;color:var(--indigo);
    font-weight:700;font-size:10.5px;letter-spacing:.2px;border-radius:6px;padding:5px 11px;cursor:pointer}
  .selected-booking .sb-change:hover{background:var(--indigo);color:#fff}

  .toolbar-btn{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;border:none;
    border-radius:8px;padding:11px;font-family:'Inter';font-size:13px;font-weight:700;cursor:pointer;margin-bottom:8px}
  .toolbar-btn.print{background:var(--indigo);color:#fff}
  .toolbar-btn.print:hover{background:var(--indigo-dk)}
  .toolbar-btn.reset{background:#fff;border:1.5px solid var(--line);color:var(--sub)}

  /* ================= TICKET PREVIEW (mirrors printed design) ================= */
  .sheet{width:800px;max-width:100%;margin:22px 0;background:var(--paper);padding:46px 50px 40px;
    box-shadow:0 6px 24px rgba(60,50,120,.10);position:relative}
  .sheet::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:var(--indigo)}

  /* Security-paper watermark, sunk behind every in-flow element via negative
     z-index — only shows through in the white space around the content. */
  .watermark{position:absolute;inset:0;z-index:-1;display:flex;flex-direction:column;align-items:center;
    justify-content:center;gap:10px;overflow:hidden;pointer-events:none;transform:rotate(-27deg)}
  .watermark svg{width:104px;height:104px;color:var(--indigo);opacity:.05}
  .watermark .wm-text{font-family:'Manrope';font-weight:800;font-size:56px;letter-spacing:12px;
    color:var(--indigo);opacity:.045;white-space:nowrap}

  /* Letterhead band — bleeds edge-to-edge across the sheet (cancels .sheet's
     own padding with negative margins) instead of sitting as an inset card.
     A bright, pale sky photo washed with soft white so it reads as a gentle
     texture behind the (dark, normal-colour) logo and text, not a moody scrim. */
  .header-block{position:relative;overflow:hidden;margin:-46px -50px 28px;padding:32px 50px 26px;
    background-image:url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1600&q=85&auto=format&fit=crop');
    background-size:cover;background-position:center 45%}
  .header-block::before{content:"";position:absolute;inset:0;
    background:linear-gradient(115deg, rgba(255,255,255,.86) 0%, rgba(245,244,253,.72) 45%, rgba(255,255,255,.88) 100%)}
  .header-block::after{content:"";position:absolute;left:0;right:0;bottom:0;height:34px;
    background:linear-gradient(to bottom,rgba(255,255,255,0) 0%,var(--paper) 100%)}

  header{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding-bottom:6px}
  header .logo img{height:44px;display:block;filter:drop-shadow(0 1px 2px rgba(43,48,64,.12))}
  header .services{font-size:10.5px;color:var(--sub);margin-top:9px;letter-spacing:.2px;font-weight:600}
  header .contacts{display:flex;align-items:center;justify-content:flex-end;gap:16px;flex-wrap:wrap;margin-top:10px}
  header .c-row{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:var(--sub);white-space:nowrap}
  header .c-row b{color:var(--ink);font-weight:800}
  header .c-row .ic{flex-shrink:0;width:21px;height:21px;border-radius:6px;display:flex;align-items:center;
    justify-content:center;background:rgba(91,86,214,.12);color:var(--indigo)}
  header .c-row.wa .ic{background:rgba(63,161,104,.14);color:#3fa168}
  header .c-row .ic svg{width:12px;height:12px}

  /* prominent centered document title, sat between the contact row and the
     agent card — the header's one clear statement of what this document is */
  .doc-title{position:relative;z-index:1;display:flex;align-items:center;gap:16px;
    justify-content:center;margin:6px 0 18px}
  .doc-title .dt-line{flex:0 1 64px;height:1px;background:linear-gradient(90deg,transparent,rgba(91,86,214,.55))}
  .doc-title .dt-line:last-child{background:linear-gradient(90deg,rgba(91,86,214,.55),transparent)}
  .doc-title .dt-text{font-family:'Manrope';font-weight:800;font-size:19px;letter-spacing:2.5px;
    text-transform:uppercase;color:var(--ink);white-space:nowrap}

  .agent{position:relative;z-index:1;display:flex;align-items:center;gap:18px;flex-wrap:wrap;
    padding:20px 24px;border-radius:8px;border:1px solid rgba(91,86,214,.16);
    background:rgba(255,255,255,.92);
    box-shadow:0 6px 18px rgba(60,50,120,.10)}
  .agent .id{padding-right:18px;border-right:1px solid rgba(32,8,160,.18)}
  .agent .nm{font-family:'Manrope';font-weight:800;font-size:17px;color:var(--ink);line-height:1.3}
  .agent .cts{display:flex;align-items:center;gap:20px;flex-wrap:wrap;margin-left:auto}
  .agent .it{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--sub)}
  .agent .it .ic{flex-shrink:0;width:23px;height:23px;border-radius:6px;display:flex;align-items:center;justify-content:center;
    background:rgba(51,46,143,.1);color:var(--indigo)}
  .agent .it.wa .ic{background:rgba(37,171,96,.14);color:#25ab60}
  .agent .it .ic svg{width:13px;height:13px}

  .sec{font-family:'Manrope';font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;
    color:var(--ink);padding-bottom:10px;margin:34px 0 18px;border-bottom:2px solid var(--indigo);
    display:flex;align-items:center;gap:8px}
  .sec .ic{flex-shrink:0;width:20px;height:20px;border-radius:6px;background:var(--indigo);color:#fff;
    display:flex;align-items:center;justify-content:center}
  .sec .ic svg{width:11px;height:11px}
  .sec .n{margin-left:auto;font-size:9.5px;font-weight:700;letter-spacing:.4px;text-transform:none;color:var(--faint)}

  table.pax{width:100%;border-collapse:collapse}
  table.pax th{font-family:'Manrope';font-size:8.5px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;
    color:var(--faint);text-align:left;padding:0 12px 8px;border-bottom:1px solid var(--line)}
  table.pax td{padding:14px 12px;font-size:13px;border-bottom:1px solid var(--line2);vertical-align:top}
  table.pax .bag{white-space:pre-line}
  table.pax tr:last-child td{border-bottom:1px solid var(--line)}
  table.pax .pname{font-weight:700}
  table.pax .ptype{display:block;font-size:8.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--faint);margin-top:1px}
  table.pax .tkt{font-variant-numeric:tabular-nums;font-weight:700;color:var(--indigo)}
  .empty-note{font-size:11.5px;color:var(--faint);font-style:italic;padding:8px 2px}

  /* Flight itinerary — paste/drop placeholder replaces the old typed-fields
     grid: agents crop the confirmation/GDS screenshot and paste it straight
     onto the ticket instead of retyping every flight detail. */
  .itin-drop{border:2px dashed rgba(91,86,214,.35);border-radius:12px;
    background:linear-gradient(180deg,var(--mist),#fff);padding:38px 28px;
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:9px;
    text-align:center;cursor:pointer;transition:border-color .15s,background .15s;min-height:170px}
  .itin-drop:hover,.itin-drop:focus,.itin-drop.drag{outline:none;border-color:var(--indigo);background:rgba(91,86,214,.06)}
  .itin-drop .idi{width:46px;height:46px;border-radius:12px;background:rgba(91,86,214,.10);color:var(--indigo);
    display:flex;align-items:center;justify-content:center;margin-bottom:2px}
  .itin-drop .idi svg{width:22px;height:22px}
  .itin-drop .idt{font-family:'Manrope';font-weight:800;font-size:14px;color:var(--ink)}
  .itin-drop .ids{font-size:11.5px;color:var(--sub);max-width:380px;line-height:1.55}
  .itin-drop .ids b{color:var(--ink)}
  .itin-drop .idk{font-size:9.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--indigo);
    background:rgba(91,86,214,.10);border-radius:5px;padding:4px 10px;margin-top:3px}

  .itin-list{display:flex;flex-direction:column;gap:14px}

  .itin-image{position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--line);
    background:var(--mist);box-shadow:0 2px 10px rgba(60,50,120,.08)}
  .itin-image img{display:block;width:100%;height:auto}
  .itin-controls{position:absolute;top:10px;right:10px;display:flex;gap:6px}
  .itin-controls button{border:none;border-radius:6px;background:rgba(43,48,64,.72);color:#fff;font-size:10.5px;
    font-weight:700;padding:6px 11px;cursor:pointer;backdrop-filter:blur(2px)}
  .itin-controls button:hover{background:rgba(43,48,64,.92)}

  .itin-add{border:1.5px dashed rgba(91,86,214,.35);border-radius:10px;padding:14px;text-align:center;
    font-size:11.5px;font-weight:700;color:var(--indigo);cursor:pointer;background:var(--mist);
    transition:border-color .15s,background .15s}
  .itin-add:hover,.itin-add:focus,.itin-add.drag{outline:none;border-color:var(--indigo);background:rgba(91,86,214,.06)}

  ol.terms{list-style:none;counter-reset:t;padding:0;margin:0}
  ol.terms li{counter-increment:t;position:relative;padding:0 0 15px 32px;font-size:11.5px;line-height:1.65;color:#333952}
  ol.terms li::before{content:counter(t);position:absolute;left:0;top:0;width:19px;height:19px;border-radius:6px;
    background:var(--mist);border:1px solid var(--line);color:var(--indigo);font-family:'Manrope';font-weight:800;
    font-size:9.5px;display:flex;align-items:center;justify-content:center}
  ol.terms a{color:var(--indigo);font-weight:700;text-decoration:none;border-bottom:1px solid rgba(51,46,143,.35)}
  ol.terms b{color:var(--ink)}

  /* Footer letterhead band — same full-bleed photo treatment as the header. */
  footer{position:relative;margin:34px -50px -40px;padding:26px 50px 30px;
    background:var(--mist);border-top:1px solid var(--line);
    display:flex;align-items:center}
  .foot-inner{width:100%;display:flex;flex-direction:column;gap:14px}
  .foot-row{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}

  .badges{display:flex;align-items:center;gap:18px}
  .badge{display:flex;align-items:center;gap:7px}
  .badge .bi{flex-shrink:0;width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;
    background:rgba(91,86,214,.10);color:var(--indigo)}
  .badge .bi svg{width:14px;height:14px}
  .badge .bt{font-family:'Manrope';font-weight:800;font-size:10.5px;color:var(--ink);letter-spacing:.2px;line-height:1.2}
  .badge .bt small{display:block;font-size:8px;font-weight:700;color:var(--faint);letter-spacing:.4px;text-transform:uppercase}
  /* Full standalone accreditation marks (ATOL roundel, IATA globe+wings) —
     shown as-is rather than icon+text pairs, matching how agencies actually
     display them. */
  .badge-logo{flex-shrink:0}
  .badge-logo img{display:block}
  .badge-logo.atol img{height:44px;width:auto}
  .badge-logo.iata img{height:34px;width:auto}

  /* payment network marks — flat wordmark/glyph cards on white, no external
     logo files (kept legally safe: stylised, not exact trademark artwork) */
  .pay{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .pay .pay-label{font-size:8.5px;font-weight:700;letter-spacing:.6px;
    text-transform:uppercase;color:var(--faint);margin-right:2px}
  .pay-chip{display:flex;align-items:center;justify-content:center;height:26px;min-width:44px;padding:0 10px;
    border-radius:6px;background:#fff;border:1px solid var(--line);box-shadow:0 1px 3px rgba(43,48,64,.08)}
  .pay-chip.visa{color:#1a1f71;font-family:'Manrope';font-weight:800;font-size:12px;font-style:italic;letter-spacing:.5px}
  .pay-chip.amex{background:#1478c8;border-color:#1478c8;color:#fff;font-family:'Manrope';font-weight:800;font-size:9px;letter-spacing:.5px}
  .pay-chip.stripe{color:#635bff;font-family:'Manrope';font-weight:800;font-size:12px;letter-spacing:.2px}
  .pay-chip.mc{gap:0}
  .pay-chip.mc .mc-dots{display:flex}
  .pay-chip.mc .mc-dots span{width:14px;height:14px;border-radius:50%}
  .pay-chip.mc .mc-dots span:first-child{background:#eb5b28}
  .pay-chip.mc .mc-dots span:last-child{background:#f2a15c;margin-left:-5px;mix-blend-mode:multiply}
  .pay-chip.paypal{font-family:'Manrope';font-weight:800;font-size:11px;letter-spacing:.2px}
  .pay-chip.paypal .pp1{color:#003087}
  .pay-chip.paypal .pp2{color:#009cde;margin-left:-1px}
  .pay-chip.apple{display:flex;align-items:center;gap:3px;color:#111;font-family:'Manrope';font-weight:700;font-size:11px}
  .pay-chip.apple svg{width:11px;height:11px}
  .pay-chip.klarna{background:#ffb3c7;border-color:#ffb3c7;color:#17120f;font-family:'Manrope';font-weight:800;font-size:11px;font-style:italic}
  .pay-chip.clearpay{background:#b2fce4;border-color:#b2fce4;color:#0b1f1a;font-family:'Manrope';font-weight:800;font-size:10px;letter-spacing:.1px}

  footer .visit{font-size:11px;font-weight:600;color:var(--ink);text-align:right}
  footer .visit a{color:var(--indigo);font-weight:800;text-decoration:none}
  footer .protected{font-size:9.5px;color:var(--sub);margin-top:2px}

  @page{size:A4;margin:14mm 13mm}
  @media print{
    html,body{background:#fff}
    .left,.toolbar-btn{display:none !important}
    .shell{display:block}
    .right{background:#fff;display:block}
    /* .panel's on-screen scroll box (max-height:100vh + overflow-y:auto) clips
       anything past one viewport height in Chromium's print engine too — without
       this override, everything below the first "page" of content is silently
       dropped instead of flowing onto page 2. */
    .panel{max-height:none !important;overflow:visible !important;padding:0 !important}
    .sheet{margin:0;box-shadow:none;width:auto}
    .sheet::before{display:none}
    .itin-controls,.itin-add{display:none !important}
    .itin-drop .idk{display:none}
    .itin-image{border:none;border-radius:0;background:none;box-shadow:none}
    .agent,.header-block,footer,table.pax tr,.itin-image{break-inside:avoid;page-break-inside:avoid}
    .sec{break-after:avoid;page-break-after:avoid}
    *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  }
</style>
</head>
@php
  // Small monochrome inline-SVG icon set — used across the header, the
  // consultant card and the booking picker. Inline so it survives print/PDF
  // without depending on an icon font loading in time.
  $icon = [
    'ticket'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>',
    'user'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'phone'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.2 4.79 1.2h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99C17.19 3.03 14.7 2 12.04 2zm5.8 14.11c-.24.68-1.4 1.32-1.93 1.4-.5.08-1.13.11-1.83-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.79-4.17-4.94-4.36-.15-.2-1.18-1.57-1.18-3 0-1.42.75-2.12 1.02-2.41.27-.29.58-.36.78-.36.2 0 .39 0 .56.01.18.01.42-.07.66.5.24.58.83 2 .9 2.15.07.15.11.32.02.51-.09.19-.14.31-.28.48-.14.16-.29.36-.42.49-.14.14-.28.29-.12.57.16.28.73 1.2 1.56 1.94 1.07.95 1.98 1.25 2.26 1.39.28.14.44.12.6-.07.16-.19.68-.79.86-1.06.18-.27.36-.22.6-.13.24.09 1.55.73 1.82.86.27.13.44.19.51.3.07.11.07.62-.17 1.3z"/></svg>',
    'mail'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>',
    'users'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'plane'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-1 0-1.3.4l-.7.9c-.4.4-.2 1.1.3 1.3L9 11l-2 3H4l-1 1.5 3.5 1.5 1.5 3.5L9 19v-3l3-2 2.1 6.2c.2.5.9.7 1.3.3l.9-.7c.4-.3.5-.8.4-1.3z"/></svg>',
    'shield'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
    'doc'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6M9 9h1"/></svg>',
    'image'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
    'globe'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'award'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/></svg>',
    'lock'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    'apple'    => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.7 12.7c0-3 2.4-4.4 2.5-4.5-1.4-2-3.5-2.3-4.2-2.3-1.8-.2-3.5 1-4.4 1-.9 0-2.3-1-3.8-1-1.9 0-3.7 1.1-4.7 2.9-2 3.5-.5 8.6 1.4 11.5 1 1.4 2.1 3 3.6 2.9 1.4-.1 2-.9 3.7-.9s2.2.9 3.7.9c1.5 0 2.5-1.4 3.5-2.8.9-1.3 1.4-2.7 1.4-2.7-1.7-.6-2.7-2.3-2.7-4.2z"/><path d="M13.9 4.2c.8-1 1.4-2.4 1.2-3.7-1.2 0-2.6.8-3.5 1.8-.7.8-1.4 2.2-1.2 3.6 1.4.1 2.7-.7 3.5-1.7z"/></svg>',
  ];

  // Standalone page (no shared sidebar/navbar), so it needs its own way back —
  // same role → dashboard-route split the main sidebar uses (verticalMenu.json).
  $backUrl = in_array(auth()->user()?->role, ['admin', 'manager']) ? url('/') : url('/my-dashboard');
@endphp
<body x-data="eticketBuilder()" @paste.window="onItinPaste($event)">

<div class="shell">
  <!-- ================= LEFT: FORM ================= -->
  <div class="panel left">
    <a href="{{ $backUrl }}" class="back-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      Back to MIS
    </a>
    <div class="brandbar">
      <img src="{{ asset('images/eticket-logo.png') }}" alt="Travel Orbit">
      <div class="t">E-Ticket Builder<small>Standalone: nothing here saves to the booking</small></div>
    </div>

    <button type="button" class="toolbar-btn print" @click="print()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="#fff"><path d="M18 3H6v6h12V3zM6 21h12v-6H6v6zM4 8h16a2 2 0 012 2v6a2 2 0 01-2 2h-2v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2H4a2 2 0 01-2-2v-6a2 2 0 012-2z"/></svg>
      Print / Save as PDF
    </button>
    <button type="button" class="toolbar-btn reset" @click="resetAll()">Clear Form</button>

    <div class="fieldset">
      <h3>{!! $icon['ticket'] !!}Load From Booking</h3>
      <template x-if="!selectedBooking">
        <div class="search-wrap">
          <input type="text" autocomplete="off" placeholder="Search by booking reference…" x-model="query"
                 @focus="if (query.trim()) showDropdown = true" @input="showDropdown = query.trim().length > 0"
                 @click.outside="showDropdown = false"
                 style="width:100%;border:1px solid var(--line);border-radius:7px;padding:8px 10px;font-size:12.5px;">
          <div class="search-results" x-show="showDropdown && query.trim().length > 0" x-cloak>
            <template x-for="b in filteredBookings" :key="b.id">
              <button type="button" @click="pickBooking(b)" x-text="b.label"></button>
            </template>
            <div class="empty" x-show="filteredBookings.length === 0">No matching bookings</div>
          </div>
        </div>
      </template>
      <template x-if="selectedBooking">
        <div class="selected-booking">
          <div class="sb-icon">{!! $icon['ticket'] !!}</div>
          <div class="sb-info">
            <div class="sb-ref" x-text="selectedBooking.ref"></div>
            <div class="sb-name">{!! $icon['user'] !!}<span x-text="selectedBooking.customer"></span></div>
          </div>
          <button type="button" class="sb-change" @click="clearSelection()">Change</button>
        </div>
      </template>
      <div class="loading-tag" x-show="loading" x-cloak>Loading booking data…</div>
    </div>


    <div class="fieldset">
      <h3>{!! $icon['user'] !!}Travel Consultant</h3>
      <div class="frow">
        <div class="field"><label>Name</label><input type="text" autocomplete="off" x-model="agentName"></div>
        <div class="field"><label>Email</label><input type="text" autocomplete="off" x-model="agentEmail"></div>
      </div>
      <div class="frow">
        <div class="field"><label>Phone</label><input type="text" autocomplete="off" x-model="agentPhone"></div>
        <div class="field"><label>WhatsApp</label><input type="text" autocomplete="off" x-model="agentWhatsapp"></div>
      </div>
    </div>

    <div class="fieldset">
      <h3>{!! $icon['users'] !!}Passengers <span class="cnt" x-text="passengers.length"></span></h3>
      <template x-for="(p, i) in passengers" :key="i">
        <div class="pax-card">
          <button type="button" class="card-remove" @click="removePassenger(i)" x-show="passengers.length > 1">✕</button>
          <div class="frow">
            <div class="field"><label>Full Name</label><input type="text" autocomplete="off" x-model="p.name" placeholder="James Anderson"></div>
            <div class="field"><label>Type</label>
              <select x-model="p.type"><option>Adult</option><option>Youth</option><option>Child</option><option>Infant</option></select>
            </div>
          </div>
          <div class="frow">
            <div class="field"><label>E-Ticket Number</label><input type="text" autocomplete="off" x-model="p.eticket" placeholder="176-1234567890"></div>
            <div class="field"><label>Airline Ref</label><input type="text" autocomplete="off" x-model="p.airlineRef" placeholder="Per-passenger, if it differs"></div>
          </div>
          <div class="frow c1">
            <div class="field"><label>Baggage Allowance</label><textarea rows="2" x-model="p.baggage" placeholder="2 x 23kg + 7kg cabin"></textarea></div>
          </div>
        </div>
      </template>
      <button type="button" class="add-btn" @click="addPassenger()">+ Add Passenger</button>
    </div>

  </div>

  <!-- ================= RIGHT: LIVE PREVIEW ================= -->
  <div class="panel right">
    <div class="sheet">
      <div class="watermark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21,16v-2l-8-5V3.5C13,2.67,12.33,2,11.5,2S10,2.67,10,3.5V9l-8,5v2l8-2.5V19l-2.5,1.5V22l3.5-1L14.5,22v-1.5L12,19v-5.5L21,16z"/></svg>
        <div class="wm-text">TRAVEL ORBIT</div>
      </div>
      <div class="header-block">
        <header>
          <div class="logo">
            <img src="{{ asset('images/eticket-logo.png') }}" alt="Travel Orbit UK">
            <div class="services">Flight&nbsp; ·&nbsp; Hotel&nbsp; ·&nbsp; Holiday&nbsp; ·&nbsp; Book Now, Pay Later</div>
          </div>
          <div class="contacts">
            <div class="c-row"><span class="ic">{!! $icon['phone'] !!}</span><b x-text="brand.mainPhone"></b></div>
            <div class="c-row wa"><span class="ic">{!! $icon['whatsapp'] !!}</span><span x-text="brand.waPhone"></span></div>
            <div class="c-row"><span class="ic">{!! $icon['mail'] !!}</span><span x-text="brand.supportEmail"></span></div>
          </div>
        </header>

        <div class="doc-title">
          <span class="dt-line"></span>
          <span class="dt-text">Electronic Ticket Record</span>
          <span class="dt-line"></span>
        </div>

        <div class="agent">
          <div class="id">
            <div class="nm" x-text="agentName || 'Travel Orbit Team'"></div>
          </div>
          <div class="cts">
            <div class="it"><span class="ic">{!! $icon['phone'] !!}</span><span x-text="agentPhone || brand.mainPhone"></span></div>
            <div class="it wa"><span class="ic">{!! $icon['whatsapp'] !!}</span><span x-text="agentWhatsapp || brand.waPhone"></span></div>
            <div class="it"><span class="ic">{!! $icon['mail'] !!}</span><span x-text="agentEmail || brand.supportEmail"></span></div>
          </div>
        </div>
      </div>

      <div class="sec"><span class="ic">{!! $icon['users'] !!}</span>Electronic Ticket Record <span class="n" x-text="passengers.length + ' ' + (passengers.length === 1 ? 'Traveller' : 'Travellers')"></span></div>
      <table class="pax">
        <thead><tr><th style="width:34px;">#</th><th>Traveller</th><th>E-Ticket Number</th><th>Baggage</th><th>Airline Ref</th></tr></thead>
        <tbody>
          <template x-for="(p, i) in passengers" :key="i">
            <tr>
              <td x-text="String(i+1).padStart(2,'0')"></td>
              <td><span class="pname" x-text="p.name || 'N/A'"></span><span class="ptype" x-text="p.type"></span></td>
              <td class="tkt" x-text="p.eticket || 'N/A'"></td>
              <td class="bag" x-text="p.baggage || 'Confirm with airline'"></td>
              <td class="tkt" x-text="p.airlineRef || 'N/A'"></td>
            </tr>
          </template>
        </tbody>
      </table>

      <div class="sec"><span class="ic">{!! $icon['plane'] !!}</span>Flight Itinerary
        <span class="n" x-show="itineraryImages.length" x-text="itineraryImages.length + ' ' + (itineraryImages.length === 1 ? 'Image' : 'Images')"></span>
      </div>

      <input type="file" accept="image/*" multiple x-ref="itinFile" style="display:none" @change="onItinFile($event)">

      <template x-if="itineraryImages.length === 0">
        <div class="itin-drop" :class="{drag: dragging}" tabindex="0" role="button"
             @click="$refs.itinFile.click()"
             @keydown.enter="$refs.itinFile.click()"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="onItinDrop($event)">
          <div class="idi">{!! $icon['image'] !!}</div>
          <div class="idt">Paste your flight itinerary screenshot here</div>
          <div class="ids">Press <b>Ctrl+V</b> anywhere on this page to paste a copied screenshot — or drag &amp; drop / click here to upload an image. Add as many as you need (e.g. outbound + return) — crop each straight from the airline confirmation or GDS.</div>
          <span class="idk">Ctrl+V to Paste</span>
        </div>
      </template>

      <template x-if="itineraryImages.length > 0">
        <div class="itin-list">
          <template x-for="(img, i) in itineraryImages" :key="i">
            <div class="itin-image">
              <img :src="img" alt="Flight itinerary screenshot">
              <div class="itin-controls">
                <button type="button" @click="removeItinImage(i)">Remove</button>
              </div>
            </div>
          </template>
          <div class="itin-add" :class="{drag: dragging}" tabindex="0" role="button"
               @click="$refs.itinFile.click()"
               @keydown.enter="$refs.itinFile.click()"
               @dragover.prevent="dragging = true"
               @dragleave.prevent="dragging = false"
               @drop.prevent="onItinDrop($event)">
            + Add another screenshot — click, drag &amp; drop, or press Ctrl+V
          </div>
        </div>
      </template>

      <div class="sec"><span class="ic">{!! $icon['doc'] !!}</span>Terms &amp; Conditions</div>
      <ol class="terms">
        <li>Positive photo identification is required at check-in and may be requested at any point during your journey. Names on your ticket must exactly match your passport.</li>
        <li>Always re-check your flight schedule 48–72 hours prior to departure by calling the airport desk or the airline directly.</li>
        <li>Please reach the airport at least 3 hours before departure and check in on time. In case of any difficulty or delay at check-in, contact the airline help desk immediately.</li>
        <li>Keep a copy of your itinerary with you at all times and ensure you hold valid travel documents for every country on your route.</li>
        <li>Direct flights may involve a &ldquo;ground elapse&rdquo;: a short stop at a foreign airport, usually for refuelling (approx. under an hour; de-boarding not required).</li>
        <li>For seating, meal requirements, or special assistance during the flight, kindly contact the airline directly (phone numbers available with us).</li>
        <li>For visas, transits, and travel documents, always confirm in good time with the relevant embassy or consulate. The agency and airline are not responsible in the absence of valid travel documents.</li>
        <li>Please raise any question about your reservation, invoice, or payment <b>before</b> tickets are issued. All tickets are non-refundable, non-transferable, and non-changeable. For full booking conditions, visit <a :href="brand.conditionsUrl" x-text="brand.website + '/terms/'"></a> or call your agent.</li>
      </ol>

      <footer>
        <div class="foot-inner">
          <div class="foot-row">
            <div class="badges">
              <div class="badge-logo atol"><img src="{{ asset('images/atol-logo.png') }}" alt="ATOL Protected"></div>
              <div class="badge-logo iata"><img src="{{ asset('images/iata-logo.png') }}" alt="IATA"></div>
            </div>
            <div class="pay">
              <span class="pay-label">We Accept</span>
              <span class="pay-chip visa">VISA</span>
              <span class="pay-chip mc"><span class="mc-dots"><span></span><span></span></span></span>
              <span class="pay-chip amex">AMEX</span>
              <span class="pay-chip stripe">stripe</span>
              <span class="pay-chip klarna">Klarna</span>
              <span class="pay-chip clearpay">Clearpay</span>
            </div>
          </div>
          <div class="foot-row">
            <div class="protected">Your trip is financially protected · Travel with confidence</div>
            <div class="visit">Book online at <a :href="brand.websiteUrl" x-text="brand.website"></a></div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</div>

<script>
function eticketBuilder() {
  return {
    bookings: @json($bookings),
    query: '',
    showDropdown: false,
    loading: false,
    selectedBooking: null,

    brand: {
      mainPhone: '020 3932 3459', waPhone: '07853 072479', supportEmail: 'info@travelorbit.co.uk',
      website: 'travelorbit.co.uk', websiteUrl: 'https://travelorbit.co.uk',
      conditionsUrl: 'https://travelorbit.co.uk/terms/',
    },

    agentName: '', agentPhone: '', agentWhatsapp: '', agentEmail: '',
    passengers: [],
    itineraryImages: [],
    dragging: false,

    init() {
      this.passengers = [this.blankPassenger()];
    },

    get filteredBookings() {
      const q = this.query.trim().toLowerCase();
      if (!q) return [];
      const list = this.bookings.filter(b => b.reference.toLowerCase().includes(q));
      return list.slice(0, 50);
    },

    async pickBooking(b) {
      this.query = b.label;
      this.showDropdown = false;
      this.loading = true;
      try {
        const res = await fetch(`/eticket/data/${b.id}`);
        const data = await res.json();
        this.agentName = data.agentName;
        this.agentPhone = data.agentPhone;
        this.agentWhatsapp = data.agentWhatsapp;
        this.agentEmail = data.agentEmail;
        const first = data.segments[0];
        const defaultBaggage = first ? (first.baggage || '') : '';
        this.passengers = data.passengers.length
          ? data.passengers.map(p => ({ ...p, baggage: p.baggage || defaultBaggage }))
          : [this.blankPassenger()];
        this.selectedBooking = { ref: data.bookingRef, customer: data.customerName || 'Unnamed' };
      } finally {
        this.loading = false;
      }
    },

    clearSelection() {
      this.selectedBooking = null;
      this.query = '';
      this.showDropdown = true;
    },

    blankPassenger() {
      return { name: '', type: 'Adult', eticket: '', airlineRef: '', baggage: '' };
    },
    addPassenger() { this.passengers.push(this.blankPassenger()); },
    removePassenger(i) { this.passengers.splice(i, 1); },

    resetAll() {
      if (!confirm('Clear the whole form?')) return;
      this.query = ''; this.selectedBooking = null;
      this.agentName = ''; this.agentPhone = ''; this.agentWhatsapp = ''; this.agentEmail = '';
      this.passengers = [this.blankPassenger()];
      this.itineraryImages = [];
    },

    onItinFile(e) {
      Array.from(e.target.files || []).forEach(f => this.readItinFile(f));
      e.target.value = '';
    },
    onItinDrop(e) {
      this.dragging = false;
      Array.from(e.dataTransfer.files || []).forEach(f => this.readItinFile(f));
    },
    onItinPaste(e) {
      const items = e.clipboardData && e.clipboardData.items;
      if (!items) return;
      let handled = false;
      for (const item of items) {
        if (item.type.startsWith('image/')) {
          const file = item.getAsFile();
          if (file) { this.readItinFile(file); handled = true; }
        }
      }
      if (handled) e.preventDefault();
    },
    readItinFile(file) {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = () => { this.itineraryImages.push(reader.result); };
      reader.readAsDataURL(file);
    },
    removeItinImage(i) { this.itineraryImages.splice(i, 1); },

    print() { window.print(); },
  };
}
</script>

</body>
</html>
