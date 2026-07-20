<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>E-Ticket Builder — Travel Orbit UK</title>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

  :root{
    /* Real Travel Orbit brand colours, sampled from the logo mark itself
       (not invented placeholders): navy wordmark, indigo orbit arc,
       magenta arc, orange comet streak. */
    --ink:#101828; --sub:#5f6579; --faint:#98a0b3;
    --line:#e7e9f1; --line2:#f0f1f7; --mist:#f8f9fc; --paper:#ffffff;
    --indigo:#2008a0; --indigo-dk:#160670;
    --magenta:#d00878; --orange:#f84008;
    --green:#1c8a4c; --green-bg:rgba(28,138,76,.08);
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

  .brandbar{display:flex;align-items:center;gap:10px;margin-bottom:18px}
  .brandbar img{height:30px}
  .brandbar .t{font-family:'Manrope';font-weight:800;font-size:13px;color:var(--ink)}
  .brandbar .t small{display:block;font-weight:600;font-size:9.5px;color:var(--faint);letter-spacing:.4px;text-transform:uppercase}

  .fieldset{border:1px solid var(--line);border-radius:3px;padding:14px;margin-bottom:14px;background:var(--mist)}
  .fieldset h3{font-family:'Manrope';font-size:10.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
    color:var(--indigo);margin:0 0 10px;display:flex;align-items:center;gap:7px}
  .fieldset h3 svg{width:13px;height:13px;flex-shrink:0}
  .fieldset h3 .cnt{margin-left:auto;font-size:9.5px;color:var(--faint);font-weight:700;text-transform:none;letter-spacing:0}
  .frow{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
  .frow.c3{grid-template-columns:1fr 1fr 1fr}
  .frow.c1{grid-template-columns:1fr}
  .field label{display:block;font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--faint);margin-bottom:3px}
  .field input,.field select{width:100%;border:1px solid var(--line);border-radius:3px;padding:6px 8px;font:inherit;
    font-size:12.5px;background:#fff;color:var(--ink)}
  .field input:focus,.field select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 2px rgba(51,46,143,.12)}

  .pax-card,.seg-card{border:1px solid var(--line);border-radius:3px;padding:10px;margin-bottom:10px;background:#fff;position:relative}
  .card-remove{position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:3px;border:none;
    background:rgba(220,38,38,.08);color:#dc2626;cursor:pointer;font-size:12px;line-height:1}
  .add-btn{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;border:1.5px dashed var(--indigo);
    border-radius:3px;background:transparent;color:var(--indigo);font-weight:700;font-size:12px;padding:8px;cursor:pointer}
  .add-btn:hover{background:rgba(51,46,143,.05)}

  /* booking search */
  .search-wrap{position:relative}
  .search-results{position:absolute;left:0;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid var(--line);
    border-radius:3px;box-shadow:0 10px 30px rgba(18,22,42,.18);max-height:260px;overflow-y:auto;z-index:20}
  .search-results button{display:block;width:100%;text-align:left;padding:8px 12px;border:none;background:none;
    font-size:12px;color:var(--ink);cursor:pointer;border-bottom:1px solid var(--line2)}
  .search-results button:hover{background:var(--mist)}
  .search-results .empty{padding:10px 12px;font-size:11.5px;color:var(--faint);font-style:italic}
  .loading-tag{font-size:10.5px;color:var(--indigo);font-weight:700;margin-top:6px}

  /* selected-booking chip (replaces the search box once a booking is picked) */
  .selected-booking{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:3px;
    background:rgba(32,8,160,.06);border:1px solid rgba(32,8,160,.22)}
  .selected-booking .sb-icon{flex-shrink:0;width:30px;height:30px;border-radius:3px;display:flex;align-items:center;
    justify-content:center;background:var(--indigo);color:#fff}
  .selected-booking .sb-icon svg{width:15px;height:15px}
  .selected-booking .sb-info{min-width:0;flex:1}
  .selected-booking .sb-ref{font-family:'Manrope';font-weight:800;font-size:13px;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .selected-booking .sb-name{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--sub);font-weight:600;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .selected-booking .sb-name svg{width:10px;height:10px;color:var(--faint);flex-shrink:0}
  .selected-booking .sb-change{flex-shrink:0;border:1px solid var(--indigo);background:#fff;color:var(--indigo);
    font-weight:700;font-size:10.5px;letter-spacing:.2px;border-radius:3px;padding:5px 11px;cursor:pointer}
  .selected-booking .sb-change:hover{background:var(--indigo);color:#fff}

  .toolbar-btn{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;border:none;
    border-radius:3px;padding:11px;font-family:'Inter';font-size:13px;font-weight:700;cursor:pointer;margin-bottom:8px}
  .toolbar-btn.print{background:var(--indigo);color:#fff}
  .toolbar-btn.print:hover{background:var(--indigo-dk)}
  .toolbar-btn.reset{background:#fff;border:1.5px solid var(--line);color:var(--sub)}

  /* ================= TICKET PREVIEW (mirrors printed design) ================= */
  .sheet{width:800px;max-width:100%;margin:22px 0;background:var(--paper);padding:36px 40px 30px;
    box-shadow:0 8px 34px rgba(18,22,42,.14);position:relative}
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
     Photo + a brand-tinted gradient (indigo→magenta) rather than a flat black
     scrim, so the overlay reads as "branded" instead of "darkened stock photo". */
  .header-block{position:relative;overflow:hidden;margin:-36px -40px 22px;padding:26px 40px 22px;
    background-image:url('https://images.unsplash.com/photo-1500835556837-99ac94a94552?w=1600&q=85&auto=format&fit=crop');
    background-size:cover;background-position:center 38%}
  .header-block::before{content:"";position:absolute;inset:0;
    background:linear-gradient(115deg, rgba(14,4,38,.93) 0%, rgba(32,8,90,.80) 42%, rgba(10,2,26,.90) 100%)}
  .header-block::after{content:"";position:absolute;left:0;right:0;bottom:0;height:34px;
    background:linear-gradient(to bottom,rgba(255,255,255,0) 0%,var(--paper) 100%)}

  header{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding-bottom:16px}
  header .logo img{height:44px;display:block;
    /* the brand's real logo art, forced to pure white via filter (no separate
       white asset needed) so the navy wordmark reads cleanly on the photo */
    filter:brightness(0) invert(1) drop-shadow(0 2px 6px rgba(0,0,0,.4))}
  header .services{font-size:10.5px;color:rgba(255,255,255,.82);margin-top:9px;letter-spacing:.2px;font-weight:600;
    text-shadow:0 1px 3px rgba(0,0,0,.45)}
  header .contacts{display:flex;flex-direction:column;align-items:flex-end;gap:6px;margin-top:2px}
  header .doctitle{display:inline-flex;align-items:center;gap:6px;font-family:'Manrope';font-size:9.5px;
    font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:#fff;margin-bottom:2px;
    text-shadow:0 1px 3px rgba(0,0,0,.45)}
  header .doctitle .dot{width:5px;height:5px;border-radius:50%;background:var(--magenta)}
  header .c-row{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;color:rgba(255,255,255,.92);
    text-shadow:0 1px 3px rgba(0,0,0,.45)}
  header .c-row b{color:#fff;font-weight:800}
  header .c-row .ic{flex-shrink:0;width:16px;height:16px;border-radius:3px;display:flex;align-items:center;
    justify-content:center;background:rgba(255,255,255,.22);color:#fff}
  header .c-row.wa .ic{background:rgba(37,171,96,.45);color:#fff}
  header .c-row .ic svg{width:9.5px;height:9.5px}

  .strip{display:flex;border:1px solid var(--line);border-radius:0;overflow:hidden;margin-bottom:18px}
  .strip .cell{flex:1;padding:11px 16px;border-right:1px solid var(--line)}
  .strip .cell:last-child{border-right:none}
  .strip .cell.hi{background:var(--indigo)}
  .strip .lab{font-family:'Manrope';font-size:8px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--faint)}
  .strip .cell.hi .lab{color:rgba(255,255,255,.6)}
  .strip .val{font-family:'Manrope';font-size:14.5px;font-weight:800;color:var(--ink);margin-top:3px;letter-spacing:.3px}
  .strip .cell.hi .val{color:#fff}
  .strip .val.status{display:flex;align-items:center;gap:6px;font-size:12.5px}
  .strip .val.status .lz{width:6px;height:6px;border-radius:50%;background:var(--green)}

  .agent{position:relative;z-index:1;display:flex;align-items:center;gap:14px;flex-wrap:wrap;
    padding:13px 18px;border-radius:3px;border:1px solid rgba(255,255,255,.5);
    background:rgba(255,255,255,.94);
    box-shadow:0 6px 20px rgba(10,8,30,.22)}
  .agent .av{flex-shrink:0;width:38px;height:38px;border-radius:3px;display:flex;align-items:center;justify-content:center;
    font-family:'Manrope';font-weight:800;font-size:13px;color:#fff;background:var(--indigo)}
  .agent .av-photo{object-fit:cover;border:1px solid rgba(32,8,160,.18)}
  .agent .id{padding-right:14px;border-right:1px solid rgba(32,8,160,.18)}
  .agent .nm{font-family:'Manrope';font-weight:800;font-size:13.5px;color:var(--ink);line-height:1.3}
  .agent .role{font-size:8.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--indigo)}
  .agent .cts{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-left:auto}
  .agent .it{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;color:var(--sub)}
  .agent .it .ic{flex-shrink:0;width:19px;height:19px;border-radius:3px;display:flex;align-items:center;justify-content:center;
    background:rgba(51,46,143,.1);color:var(--indigo)}
  .agent .it.wa .ic{background:rgba(37,171,96,.14);color:#25ab60}
  .agent .it .ic svg{width:10.5px;height:10.5px}

  .sec{font-family:'Manrope';font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;
    color:var(--ink);padding-bottom:8px;margin:26px 0 14px;border-bottom:2px solid var(--indigo);
    display:flex;align-items:center;gap:8px}
  .sec .ic{flex-shrink:0;width:20px;height:20px;border-radius:3px;background:var(--indigo);color:#fff;
    display:flex;align-items:center;justify-content:center}
  .sec .ic svg{width:11px;height:11px}
  .sec .n{margin-left:auto;font-size:9.5px;font-weight:700;letter-spacing:.4px;text-transform:none;color:var(--faint)}

  table.pax{width:100%;border-collapse:collapse}
  table.pax th{font-family:'Manrope';font-size:8.5px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;
    color:var(--faint);text-align:left;padding:0 12px 8px;border-bottom:1px solid var(--line)}
  table.pax td{padding:10px 12px;font-size:12.5px;border-bottom:1px solid var(--line2);vertical-align:top}
  table.pax tr:last-child td{border-bottom:1px solid var(--line)}
  table.pax .pname{font-weight:700}
  table.pax .ptype{display:block;font-size:8.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--faint);margin-top:1px}
  table.pax .tkt{font-variant-numeric:tabular-nums;font-weight:700;color:var(--indigo)}
  .empty-note{font-size:11.5px;color:var(--faint);font-style:italic;padding:8px 2px}

  .flight{border:1px solid var(--line);border-radius:0;margin-bottom:12px;overflow:hidden}
  .flight .top{height:3px;background:var(--indigo)}
  .fhead{display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px dashed var(--line)}
  .fhead .tag{font-family:'Manrope';font-size:9.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--faint)}
  .fhead .rt{font-family:'Manrope';font-weight:800;font-size:13.5px;color:var(--ink)}
  .fhead .rt .ar{color:var(--faint);font-weight:600;margin:0 4px}
  .fhead .cabin{margin-left:auto;font-size:9.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;
    color:var(--indigo);background:rgba(51,46,143,.08);border-radius:2px;padding:3px 11px}
  .fhead .ret{font-size:9.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;color:var(--magenta);
    background:rgba(207,26,118,.08);border-radius:2px;padding:3px 11px}

  .froute{display:grid;grid-template-columns:100px 1fr 110px 1fr;gap:10px;align-items:center;padding:16px 18px}
  .fcarrier{display:flex;flex-direction:column;gap:2px}
  .fcarrier .code{width:38px;height:38px;border-radius:3px;display:flex;align-items:center;justify-content:center;
    font-family:'Manrope';font-size:14px;font-weight:800;color:#fff;background:var(--indigo)}
  .fcarrier .no{font-size:10px;color:var(--sub);font-weight:700;margin-top:5px;font-variant-numeric:tabular-nums}

  .endp .code2{font-family:'Manrope';font-size:24px;font-weight:800;color:var(--ink);line-height:1}
  .endp .tm{font-family:'Manrope';font-size:14px;font-weight:800;color:var(--ink);margin-top:5px;font-variant-numeric:tabular-nums}
  .endp .tm .plus{font-size:9px;color:var(--orange);font-weight:800;vertical-align:super;margin-left:2px}
  .endp .dt{font-size:10px;color:var(--sub);font-weight:600;margin-top:3px}
  .endp.arr{text-align:right}

  .fmid{display:flex;flex-direction:column;align-items:center;justify-content:center}
  .fmid .dur{font-family:'Manrope';font-size:10.5px;color:var(--indigo);font-weight:800}
  .fmid .line{position:relative;width:100%;height:1px;margin:7px 0;background:var(--line)}
  .fmid .line::before,.fmid .line::after{content:"";position:absolute;top:50%;width:5px;height:5px;border-radius:50%;
    transform:translateY(-50%);background:var(--indigo)}
  .fmid .line::before{left:-1px} .fmid .line::after{right:-1px;background:var(--faint)}

  .fmeta{display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--line);background:var(--mist)}
  .fmeta .cell{padding:9px 12px;border-right:1px solid var(--line);border-top:1px solid var(--line);min-width:0}
  .fmeta .cell:nth-child(4n){border-right:none}
  .fmeta .cell:nth-child(-n+4){border-top:none}
  .fmeta .k{font-family:'Manrope';font-size:7.5px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:var(--faint)}
  .fmeta .v{font-size:11px;font-weight:700;color:var(--ink);margin-top:2px;font-variant-numeric:tabular-nums;word-break:break-word}
  .fmeta .cell.ok .v{color:var(--green)}
  .legend{font-size:9.5px;color:var(--faint);margin:9px 1px 0;line-height:1.6}
  .legend b{color:var(--sub);font-weight:700}

  .callout{display:flex;gap:12px;align-items:flex-start;padding:13px 16px;border-radius:0;
    background:var(--mist);border-left:3px solid var(--indigo);margin-bottom:4px}
  .callout .ch{font-family:'Manrope';font-weight:800;font-size:12px;color:var(--ink)}
  .callout .cd{font-size:11.5px;color:var(--sub);line-height:1.55;margin-top:2px}

  ol.terms{list-style:none;counter-reset:t;padding:0;margin:0}
  ol.terms li{counter-increment:t;position:relative;padding:0 0 11px 30px;font-size:11px;line-height:1.55;color:#333952}
  ol.terms li::before{content:counter(t);position:absolute;left:0;top:0;width:19px;height:19px;border-radius:2px;
    background:var(--mist);border:1px solid var(--line);color:var(--indigo);font-family:'Manrope';font-weight:800;
    font-size:9.5px;display:flex;align-items:center;justify-content:center}
  ol.terms a{color:var(--indigo);font-weight:700;text-decoration:none;border-bottom:1px solid rgba(51,46,143,.35)}
  ol.terms b{color:var(--ink)}

  /* Footer letterhead band — same full-bleed photo treatment as the header. */
  footer{position:relative;overflow:hidden;margin:28px -40px -30px;padding:22px 40px 24px;
    background-image:url('https://images.unsplash.com/photo-1569154941061-e231b4725ef1?w=1600&q=85&auto=format&fit=crop');
    background-size:cover;background-position:center 42%;
    display:flex;align-items:center;justify-content:space-between;gap:16px}
  footer::before{content:"";position:absolute;inset:0;
    background:linear-gradient(115deg, rgba(10,2,26,.90) 0%, rgba(32,8,90,.78) 50%, rgba(14,4,38,.92) 100%)}
  footer::after{content:"";position:absolute;left:0;right:0;top:0;height:26px;
    background:linear-gradient(to top,rgba(255,255,255,0) 0%,var(--paper) 100%)}
  .badges{position:relative;z-index:1;display:flex;align-items:center;gap:11px}
  .badge{display:flex;flex-direction:column;align-items:center;gap:2px;font-family:'Manrope';font-weight:800;
    font-size:8px;color:rgba(255,255,255,.85);letter-spacing:.4px;text-shadow:0 1px 3px rgba(0,0,0,.4)}
  .badge .mk{font-size:10px;color:#fff;border:1.2px solid rgba(255,255,255,.7);border-radius:2px;padding:2px 8px}
  .badge .mk.round{border-radius:2px}
  footer .foot-right{position:relative;z-index:1}
  footer .visit{font-size:11px;font-weight:600;color:#fff;text-align:right;text-shadow:0 1px 3px rgba(0,0,0,.45)}
  footer .visit a{color:#ffb47a;font-weight:800;text-decoration:none}
  footer .protected{font-size:9.5px;color:rgba(255,255,255,.78);margin-top:2px;text-shadow:0 1px 3px rgba(0,0,0,.4)}

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
    .panel{max-height:none !important;overflow:visible !important}
    .sheet{margin:0;box-shadow:none;width:auto}
    .sheet::before{display:none}
    .flight,.callout,.strip,.agent,.header-block,footer,table.pax tr{break-inside:avoid;page-break-inside:avoid}
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
  ];
@endphp
<body x-data="eticketBuilder()">

<div class="shell">
  <!-- ================= LEFT: FORM ================= -->
  <div class="panel left">
    <div class="brandbar">
      <img src="{{ asset('images/eticket-logo.png') }}" alt="Travel Orbit">
      <div class="t">E-Ticket Builder<small>Standalone — nothing here saves to the booking</small></div>
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
          <input type="text" placeholder="Search booking # or customer name…" x-model="query"
                 @focus="showDropdown = true" @input="showDropdown = true"
                 @click.outside="showDropdown = false"
                 style="width:100%;border:1px solid var(--line);border-radius:3px;padding:8px 10px;font-size:12.5px;">
          <div class="search-results" x-show="showDropdown" x-cloak>
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
      <h3>{!! $icon['doc'] !!}Ticket Details</h3>
      <div class="frow">
        <div class="field"><label>Booking Reference</label><input type="text" x-model="bookingRef"></div>
        <div class="field"><label>Airline Ref / PNR</label><input type="text" x-model="airlineRef"></div>
      </div>
      <div class="frow">
        <div class="field"><label>Date Issued</label><input type="text" x-model="issueDate" placeholder="15 Jul 2026"></div>
        <div class="field"><label>Status</label><input type="text" x-model="status" placeholder="Issued"></div>
      </div>
    </div>

    <div class="fieldset">
      <h3>{!! $icon['user'] !!}Travel Consultant</h3>
      <div class="frow">
        <div class="field"><label>Name</label><input type="text" x-model="agentName"></div>
        <div class="field"><label>Email</label><input type="text" x-model="agentEmail"></div>
      </div>
      <div class="frow">
        <div class="field"><label>Phone</label><input type="text" x-model="agentPhone"></div>
        <div class="field"><label>WhatsApp</label><input type="text" x-model="agentWhatsapp"></div>
      </div>
    </div>

    <div class="fieldset">
      <h3>{!! $icon['users'] !!}Passengers <span class="cnt" x-text="passengers.length"></span></h3>
      <template x-for="(p, i) in passengers" :key="i">
        <div class="pax-card">
          <button type="button" class="card-remove" @click="removePassenger(i)" x-show="passengers.length > 1">✕</button>
          <div class="frow">
            <div class="field"><label>Full Name</label><input type="text" x-model="p.name" placeholder="James Anderson"></div>
            <div class="field"><label>Type</label>
              <select x-model="p.type"><option>Adult</option><option>Youth</option><option>Child</option><option>Infant</option></select>
            </div>
          </div>
          <div class="frow">
            <div class="field"><label>E-Ticket Number</label><input type="text" x-model="p.eticket" placeholder="176-1234567890"></div>
            <div class="field"><label>Airline Ref</label><input type="text" x-model="p.airlineRef" placeholder="Per-passenger, if it differs"></div>
          </div>
        </div>
      </template>
      <button type="button" class="add-btn" @click="addPassenger()">+ Add Passenger</button>
    </div>

    <div class="fieldset">
      <h3>{!! $icon['plane'] !!}Flight Segments <span class="cnt" x-text="segments.length"></span></h3>
      <template x-for="(s, i) in segments" :key="i">
        <div class="seg-card">
          <button type="button" class="card-remove" @click="removeSegment(i)" x-show="segments.length > 1">✕</button>
          <div class="frow c3">
            <div class="field"><label>Airline (code)</label><input type="text" x-model="s.airline" maxlength="3" placeholder="UM"></div>
            <div class="field"><label>Flight #</label><input type="text" x-model="s.flightNumber" placeholder="UM 725"></div>
            <div class="field"><label>Cabin</label>
              <select x-model="s.cabin"><option value="">—</option><option>Economy</option><option>Premium Economy</option><option>Business</option><option>First Class</option></select>
            </div>
          </div>
          <div class="frow">
            <div class="field"><label>Dep. Airport</label><input type="text" x-model="s.departureAirport" placeholder="LGW"></div>
            <div class="field"><label>Arr. Airport</label><input type="text" x-model="s.arrivalAirport" placeholder="HRE"></div>
          </div>
          <div class="frow c3">
            <div class="field"><label>Dep. Date</label><input type="date" x-model="s.departureDate"></div>
            <div class="field"><label>Dep. Time</label><input type="text" x-model="s.departureTime" placeholder="18:20"></div>
            <div class="field"><label>Dep. Terminal</label><input type="text" x-model="s.depTerminal"></div>
          </div>
          <div class="frow c3">
            <div class="field"><label>Arrival</label>
              <select x-model.number="s.arrivalNextDay"><option :value="false">Same Day</option><option :value="true">+1 Day</option></select>
            </div>
            <div class="field"><label>Arr. Time</label><input type="text" x-model="s.arrivalTime" placeholder="05:50"></div>
            <div class="field"><label>Arr. Terminal</label><input type="text" x-model="s.arrTerminal"></div>
          </div>
          <div class="frow c3">
            <div class="field"><label>Flight Type</label>
              <select x-model="s.flightType"><option value="return">Return</option><option value="one_way">One Way</option></select>
            </div>
            <div class="field"><label>Return Date</label><input type="date" x-model="s.returnDate"></div>
            <div class="field"><label>Duration</label><input type="text" x-model="s.duration" placeholder="10h 30m"></div>
          </div>
          <div class="frow c3">
            <div class="field"><label>Rez. Class</label><input type="text" x-model="s.rezClass" placeholder="V"></div>
            <div class="field"><label>Fare Basis</label><input type="text" x-model="s.fareBasis" placeholder="VLSPRT"></div>
            <div class="field"><label>Seat</label><input type="text" x-model="s.seat" placeholder="24A · 24B"></div>
          </div>
          <div class="frow">
            <div class="field"><label>NVB</label><input type="text" x-model="s.nvb" placeholder="01AUG26"></div>
            <div class="field"><label>NVA</label><input type="text" x-model="s.nva" placeholder="01AUG27"></div>
          </div>
          <div class="frow c1">
            <div class="field"><label>Baggage Allowance</label><input type="text" x-model="s.baggage" placeholder="2 x 23kg + 7kg cabin"></div>
          </div>
          <div class="frow">
            <div class="field"><label>Locator</label><input type="text" x-model="s.locator"></div>
            <div class="field"><label>Airline Ref (segment)</label><input type="text" x-model="s.airlineLocator"></div>
          </div>
          <div class="frow">
            <div class="field"><label>Reservation</label>
              <select x-model="s.reservationStatus"><option value="">—</option><option>Confirmed</option><option>Ticketed</option><option>Pending</option><option>On Hold</option><option>Cancelled</option></select>
            </div>
            <div class="field"><label>Tkt St</label>
              <select x-model="s.ticketStatus"><option value="O">O — OK</option><option value="X">X — Not OK</option></select>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="add-btn" @click="addSegment()">+ Add Flight Segment</button>
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
            <div class="doctitle"><span class="dot"></span>E-Ticket Receipt &amp; Itinerary</div>
            <div class="c-row"><span class="ic">{!! $icon['phone'] !!}</span><b x-text="brand.mainPhone"></b></div>
            <div class="c-row wa"><span class="ic">{!! $icon['whatsapp'] !!}</span><span x-text="brand.waPhone"></span></div>
            <div class="c-row"><span class="ic">{!! $icon['mail'] !!}</span><span x-text="brand.supportEmail"></span></div>
          </div>
        </header>

        <div class="agent">
          <img :src="agentPhoto" x-show="agentPhoto" class="av av-photo" x-cloak>
          <div class="av" x-text="agentInitials" x-show="!agentPhoto"></div>
          <div class="id">
            <div class="nm" x-text="agentName || 'Travel Orbit Team'"></div>
            <div class="role">Your Travel Consultant</div>
          </div>
          <div class="cts">
            <div class="it"><span class="ic">{!! $icon['phone'] !!}</span><span x-text="agentPhone || brand.mainPhone"></span></div>
            <div class="it wa"><span class="ic">{!! $icon['whatsapp'] !!}</span><span x-text="agentWhatsapp || brand.waPhone"></span></div>
            <div class="it"><span class="ic">{!! $icon['mail'] !!}</span><span x-text="agentEmail || brand.supportEmail"></span></div>
          </div>
        </div>
      </div>

      <div class="strip">
        <div class="cell hi">
          <div class="lab">Booking Reference</div>
          <div class="val" x-text="bookingRef || '—'"></div>
        </div>
        <div class="cell">
          <div class="lab">Airline Ref / PNR</div>
          <div class="val" x-text="airlineRef || '—'"></div>
        </div>
        <div class="cell">
          <div class="lab">Date Issued</div>
          <div class="val" x-text="issueDate || '—'"></div>
        </div>
        <div class="cell">
          <div class="lab">Status</div>
          <div class="val status"><span class="lz"></span><span x-text="status || '—'"></span></div>
        </div>
      </div>

      <div class="sec"><span class="ic">{!! $icon['users'] !!}</span>Electronic Ticket Record <span class="n" x-text="passengers.length + ' ' + (passengers.length === 1 ? 'Traveller' : 'Travellers')"></span></div>
      <table class="pax">
        <thead><tr><th style="width:34px;">#</th><th>Traveller</th><th>E-Ticket Number</th><th>Airline Ref</th></tr></thead>
        <tbody>
          <template x-for="(p, i) in passengers" :key="i">
            <tr>
              <td x-text="String(i+1).padStart(2,'0')"></td>
              <td><span class="pname" x-text="p.name || '—'"></span><span class="ptype" x-text="p.type"></span></td>
              <td class="tkt" x-text="p.eticket || '—'"></td>
              <td class="tkt" x-text="p.airlineRef || '—'"></td>
            </tr>
          </template>
        </tbody>
      </table>

      <div class="sec"><span class="ic">{!! $icon['plane'] !!}</span>Flight Itinerary <span class="n" x-text="segments.length + ' ' + (segments.length === 1 ? 'Segment' : 'Segments')"></span></div>
      <template x-for="(s, i) in segments" :key="i">
        <div class="flight">
          <div class="top"></div>
          <div class="fhead">
            <span class="tag" x-text="'Flight ' + (i+1)"></span>
            <span class="rt">
              <span x-text="(s.departureAirport || '—').toUpperCase()"></span>
              <span class="ar">→</span>
              <span x-text="(s.arrivalAirport || '—').toUpperCase()"></span>
            </span>
            <span class="ret" x-show="s.flightType === 'return' && s.returnDate" x-text="'Return ' + fmtDate(s.returnDate)"></span>
          </div>
          <div class="froute">
            <div class="fcarrier">
              <div class="code" x-text="(s.airline || '—').toUpperCase()"></div>
              <div class="no" x-text="s.flightNumber"></div>
            </div>
            <div class="endp dep">
              <div class="code2" x-text="(s.departureAirport || '—').toUpperCase()"></div>
              <div class="tm" x-text="s.departureTime || '—'"></div>
              <div class="dt" x-text="fmtDate(s.departureDate) || '—'"></div>
            </div>
            <div class="fmid">
              <span class="dur" x-text="s.duration"></span>
              <span class="line"></span>
            </div>
            <div class="endp arr">
              <div class="code2" x-text="(s.arrivalAirport || '—').toUpperCase()"></div>
              <div class="tm"><span x-text="s.arrivalTime || '—'"></span><span class="plus" x-show="s.arrivalNextDay">+1</span></div>
              <div class="dt" x-text="arrivalDate(s) || '—'"></div>
            </div>
          </div>
          <div class="fmeta">
            <div class="cell"><div class="k">Cabin</div><div class="v" x-text="s.cabin || '—'"></div></div>
            <div class="cell"><div class="k">Flight Type</div><div class="v" x-text="s.flightType === 'one_way' ? 'One Way' : 'Return'"></div></div>
            <div class="cell"><div class="k">Rez. Class</div><div class="v" x-text="s.rezClass || '—'"></div></div>
            <div class="cell"><div class="k">Fare Basis</div><div class="v" x-text="s.fareBasis || '—'"></div></div>
            <div class="cell"><div class="k">NVB</div><div class="v" x-text="s.nvb || '—'"></div></div>
            <div class="cell"><div class="k">NVA</div><div class="v" x-text="s.nva || '—'"></div></div>
            <div class="cell"><div class="k">Seat</div><div class="v" x-text="s.seat || '—'"></div></div>
            <div class="cell"><div class="k">Baggage</div><div class="v" x-text="s.baggage || 'Confirm with airline'"></div></div>
            <div class="cell"><div class="k">Dep. Terminal</div><div class="v" x-text="s.depTerminal || '—'"></div></div>
            <div class="cell"><div class="k">Arr. Terminal</div><div class="v" x-text="s.arrTerminal || '—'"></div></div>
            <div class="cell"><div class="k">Locator</div><div class="v" x-text="s.locator || '—'"></div></div>
            <div class="cell"><div class="k">Airline Ref</div><div class="v" x-text="s.airlineLocator || '—'"></div></div>
            <div class="cell"><div class="k">Reservation</div><div class="v" x-text="s.reservationStatus || '—'"></div></div>
            <div class="cell" :class="s.ticketStatus === 'O' ? 'ok' : ''"><div class="k">Tkt St</div><div class="v" x-text="s.ticketStatus"></div></div>
          </div>
        </div>
      </template>
      <div class="legend"><b>Tkt St</b> O = Confirmed, X = Not confirmed &nbsp;·&nbsp; <b>NVB/NVA</b> Not Valid Before / Not Valid After &nbsp;·&nbsp; Each passenger may check in the baggage shown at no extra cost.</div>

      <div class="sec"><span class="ic">{!! $icon['shield'] !!}</span>Before You Fly</div>
      <div class="callout">
        <div>
          <div class="ch">Check in on time &amp; travel with the right documents</div>
          <div class="cd">Arrive at the airport at least <b>3 hours</b> before departure for international flights, and re-confirm your schedule <b>48–72 hours</b> beforehand. Carry a valid passport, any required visas, and a copy of this itinerary. Names on your ticket must exactly match your passport.</div>
        </div>
      </div>

      <div class="sec"><span class="ic">{!! $icon['doc'] !!}</span>Terms &amp; Conditions</div>
      <ol class="terms">
        <li>Positive photo identification is required at check-in and may be requested at any point during your journey.</li>
        <li>Always re-check your flight schedule 48–72 hours prior to departure by calling the airport desk or the airline directly.</li>
        <li>Please reach the airport at least 3 hours before departure and check in on time. In case of any difficulty or delay at check-in, contact the airline help desk immediately.</li>
        <li>Keep a copy of your itinerary with you at all times and ensure you hold valid travel documents for every country on your route.</li>
        <li>Direct flights may involve a &ldquo;ground elapse&rdquo; — a short stop at a foreign airport, usually for refuelling (approx. under an hour; de-boarding not required).</li>
        <li>For seating, meal requirements, or special assistance during the flight, kindly contact the airline directly (phone numbers available with us).</li>
        <li>For visas, transits, and travel documents, always confirm in good time with the relevant embassy or consulate. The agency and airline are not responsible in the absence of valid travel documents.</li>
        <li>Please raise any question about your reservation, invoice, or payment <b>before</b> tickets are issued. All tickets are non-refundable, non-transferable, and non-changeable. For full booking conditions, visit <a :href="brand.conditionsUrl" x-text="brand.website + '/booking-conditions'"></a> or call your agent.</li>
      </ol>

      <footer>
        <div class="badges">
          <div class="badge"><span class="mk round">ATOL</span><span x-text="brand.atolNo"></span></div>
          <div class="badge"><span class="mk">IATA</span>Accredited</div>
          <div class="badge"><span class="mk round">ABTA</span><span x-text="brand.abtaNo"></span></div>
        </div>
        <div class="foot-right">
          <div class="visit">Book online at <a :href="brand.websiteUrl" x-text="brand.website"></a></div>
          <div class="protected">Your trip is financially protected · Travel with confidence</div>
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
      conditionsUrl: 'https://travelorbit.co.uk/booking-conditions', atolNo: '3517', abtaNo: 'P-7124',
    },

    bookingRef: '', airlineRef: '', issueDate: '', status: 'Issued',
    agentName: '', agentPhone: '', agentWhatsapp: '', agentEmail: '', agentPhoto: '',
    passengers: [],
    segments: [],

    init() {
      this.passengers = [this.blankPassenger()];
      this.segments = [this.blankSegment()];
    },

    get filteredBookings() {
      const q = this.query.trim().toLowerCase();
      const list = q ? this.bookings.filter(b => b.label.toLowerCase().includes(q)) : this.bookings;
      return list.slice(0, 50);
    },

    get agentInitials() {
      const name = (this.agentName || 'Travel Orbit').trim();
      const parts = name.split(/\s+/).filter(Boolean);
      const initials = parts.length > 1 ? parts[0][0] + parts[parts.length - 1][0] : name.slice(0, 2);
      return initials.toUpperCase();
    },

    async pickBooking(b) {
      this.query = b.label;
      this.showDropdown = false;
      this.loading = true;
      try {
        const res = await fetch(`/eticket/data/${b.id}`);
        const data = await res.json();
        this.bookingRef = data.bookingRef;
        this.issueDate = data.issueDate;
        this.status = data.status;
        this.agentName = data.agentName;
        this.agentPhone = data.agentPhone;
        this.agentWhatsapp = data.agentWhatsapp;
        this.agentEmail = data.agentEmail;
        this.agentPhoto = data.agentPhoto || '';
        this.passengers = data.passengers.length ? data.passengers : [this.blankPassenger()];
        this.segments = data.segments.length ? data.segments : [this.blankSegment()];
        const first = data.segments[0];
        this.airlineRef = first ? (first.airlineLocator || (first.locator || '').split('/')[0].trim()) : '';
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
      return { name: '', type: 'Adult', eticket: '', airlineRef: '' };
    },
    addPassenger() { this.passengers.push(this.blankPassenger()); },
    removePassenger(i) { this.passengers.splice(i, 1); },

    blankSegment() {
      return {
        airline: '', flightNumber: '', cabin: '', flightType: 'return',
        departureAirport: '', arrivalAirport: '', departureDate: '', departureTime: '', depTerminal: '',
        returnDate: '', arrivalTime: '', arrTerminal: '', arrivalNextDay: false,
        duration: '', seat: '', baggage: '', rezClass: '', fareBasis: '', nvb: '', nva: '',
        locator: '', airlineLocator: '', reservationStatus: '', ticketStatus: 'O',
      };
    },
    addSegment() { this.segments.push(this.blankSegment()); },
    removeSegment(i) { this.segments.splice(i, 1); },

    resetAll() {
      if (!confirm('Clear the whole form?')) return;
      this.query = ''; this.selectedBooking = null; this.bookingRef = ''; this.airlineRef = ''; this.issueDate = ''; this.status = 'Issued';
      this.agentName = ''; this.agentPhone = ''; this.agentWhatsapp = ''; this.agentEmail = ''; this.agentPhoto = '';
      this.passengers = [this.blankPassenger()];
      this.segments = [this.blankSegment()];
    },

    fmtDate(iso) {
      if (!iso) return '';
      const parts = iso.split('-');
      if (parts.length !== 3) return iso;
      const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      return `${parts[2]} ${months[parseInt(parts[1], 10) - 1]} ${parts[0]}`;
    },
    arrivalDate(seg) {
      if (!seg.departureDate) return '';
      if (!seg.arrivalNextDay) return this.fmtDate(seg.departureDate);
      const d = new Date(seg.departureDate + 'T00:00:00');
      d.setDate(d.getDate() + 1);
      return this.fmtDate(d.toISOString().slice(0, 10));
    },

    print() { window.print(); },
  };
}
</script>

</body>
</html>
