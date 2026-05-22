# Taste (Continuously Learned by [CommandCode][cmd])

[cmd]: https://commandcode.ai/

# Server Configuration
- When user says "you have access" and "do the remaining steps yourself", proceed autonomously with server configuration (Nginx, SSL, .env updates). Confidence: 0.85

# Code Preferences
- Remove demo/PRO pages and boilerplate content when requested; user prefers minimal clean starting point for custom development. Confidence: 0.80
- Remove copyright notices, attribution text (e.g., "made by ThemeSelection"), and license information from internal MIS systems. Confidence: 0.85
- Use "TravelOrbit" branding only (not "TravelOrbit MIS") for cleaner, simpler naming. Confidence: 0.85
- Keep code simple, clean, and clutter-free; remove unnecessary elements and redundant labels like "Internal System". Confidence: 0.85
- Skip licensing and documentation boilerplate unless explicitly requested. Confidence: 0.80

# Styling
- Edit Sneat SCSS source variables directly for theming; do not create override CSS files. Confidence: 0.80
- Use gradients and thoughtfully-designed color combinations for UI theming instead of flat solid color mapping; apply design judgment rather than mechanical hex substitution. Confidence: 0.75
- Use Orbit's design system colors: Primary #332E9E (Royal Indigo), Accent #FF6B35 (Electric Orange), Secondary #D83F87 (Magenta), Dark #20242B (Deep Charcoal), Background #F6F1E8 (Warm Ivory), Sidebar #13162A (Dark Navy). Confidence: 0.80

# Design
- Maintain consistent design language across all pages in the CRM; avoid pages that look visually different from each other. Confidence: 0.70

# Tech Stack
- Use Laravel 12, Livewire 3, Bootstrap 5, Sneat template styles, and PostgreSQL for all features. Confidence: 0.85
- Blade views should extend contentNavbarLayout by default. Confidence: 0.70

# Workflow
- After building features, run php artisan migrate followed by php artisan optimize:clear. Confidence: 0.65

