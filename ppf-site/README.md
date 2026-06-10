# GLOSSWERK — PPF & Auto Detailing

One-page Awwwards-stijl website voor een PPF- en auto-detailingstudio.
Volledig statisch: HTML, CSS en vanilla JavaScript — geen build-stap nodig.

## Lokaal bekijken

Open `index.html` rechtstreeks in de browser, of serveer de map:

```bash
npx http-server ppf-site -p 8080
```

## Techniek

- **GSAP 3.13 + ScrollTrigger** (lokaal gevendord in `js/vendor/`) voor alle animaties
- **Lenis** voor smooth scrolling
- Fonts via Fontshare (Clash Display + Satoshi), foto's via Unsplash

## Features

- Preloader met teller en panel-reveal naar de hero
- Hero met char-voor-char titelanimatie en parallax-achtergrond
- Scroll-velocity-reactieve marquee
- Statement-sectie met woord-voor-woord scrub-reveal
- Diensten-lijst met zwevende afbeeldingspreview die de muis volgt
- Interactieve vóór/ná-vergelijker (drag, werkt ook op touch)
- Horizontale scroll-galerij met pin (desktop) / verticale lijst (mobiel)
- Stats-tellers, magnetische knoppen, custom cursor met labels
- Fullscreen mobiel menu met clip-path-animatie
- Responsive (breakpoints op 1024 / 768 / 480 px) en `prefers-reduced-motion`-ondersteuning

## Aanpassen

- Kleuren en fonts: CSS-variabelen bovenin `css/style.css` (`--accent`, `--bg`, …)
- Teksten en foto's: rechtstreeks in `index.html`
- Animatietiming: `js/main.js`
