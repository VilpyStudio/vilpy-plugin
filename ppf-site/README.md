# Kick Detailing — Premium autodetailing, PPF & ceramic coating

One-page homepage voor Kick Detailing (Zaandam), geïmplementeerd vanuit het
Claude Design-handoff-bundle "kick-detailing". Volledig statisch: HTML, CSS en
vanilla JavaScript — geen build-stap nodig.

## Lokaal bekijken

Open `index.html` rechtstreeks in de browser, of serveer de map:

```bash
npx http-server ppf-site -p 8080
```

## Design

- Donkere, warme basis (`oklch`-antraciet) met champagne-accent `rgb(212 188 140)`
- Dunne borders, glasachtige cards, veel witruimte, rustige animaties
- Typografie: Clash Display (display) + Satoshi (body) + JetBrains Mono (micro-labels)
- Geen externe foto's: alle beeldvlakken zijn "plates" — moody studio-gradients
  met sheen, grain, carrosserie-curves en een mono-label dat beschrijft welke
  foto/video daar later komt (bv. *"PPF — close-up"*)

## Secties

1. Sticky header (transparant → blur na scroll) + mobiel menu
2. Full-screen hero met video-placeholder, trust-indicators en scroll-cue
3. Vertrouwd door (partner-grid)
4. Introductie met stats-tellers en floating studio-cards
5. Diensten: PPF / Detailing / Ceramic coating (gelijkwaardige cards)
6. Waarom Kick Detailing (6 voordelen)
7. Werkproces (4 stappen, horizontale connector)
8. Projecten met werkende filters (Alles / PPF / Detailing / Ceramic coating)
9. Op locatie (extra dienst, bordered panel)
10. Voor bedrijven (sticky kolom + 6 zakelijke punten + intake-strip)
11. Eind-CTA + uitgebreide footer
12. Sticky WhatsApp/Bel-balk op mobiel

## Techniek

- **GSAP 3.13 + ScrollTrigger** (lokaal gevendord in `js/vendor/`) voor rustige reveals,
  hero-parallax, tellers en filter-transities
- **Lenis** voor smooth scrolling
- Responsive (breakpoints 640 / 768 / 1024 px), `prefers-reduced-motion`-ondersteuning

## Aanpassen

- Kleuren: CSS-variabelen bovenin `css/style.css` (`--accent`, `--bg`, …)
  - Alternatieve accenten uit het design: Goud `rgb(201 162 89)`,
    Zilver `rgb(200 204 210)`, Koel blauw `rgb(138 178 206)`
- Teksten: rechtstreeks in `index.html`
- Telefoonnummer/WhatsApp: zoek op `31600000000` en vervang
- Plates door echte foto's vervangen: vervang een `.plate`-div door een `<img>`
