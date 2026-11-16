# Vilpy

Beheer-plugin van Studio Vilpy voor WordPress-klantsites.

## Wat hij doet
- **Inlogpagina** in de huisstijl van de klant (logo, achtergrond of achtergrondkleur, accentkleur).
- **Eigen inlogadres** (standaard `/vilpy`); `wp-login.php` en `wp-admin` zijn niet direct bereikbaar.
- **Dashboard**: welkomstbanner met eigen tekst; standaard WordPress-widgets verborgen voor klanten.
- **Admin-stijl** in donker Vilpy-groen.
- **Vast gedrag**: Gutenberg uit (ook geen blok-CSS op de site), taalkeuze op de inlogpagina weg, geen emoji-scripts.
- **Elementor**: eigen pijlen en geen Elementor-iconfont voor bezoekers, via de opties `elementor-arrows` en `remove-elementor-icons` (bij activeren aan).

Instellingen: *Instellingen → Vilpy*.

## Release
1. Versie ophogen in `vilpy-vilpy.php` en `update.json` (met changelog).
2. Zip maken met de map `vilpy/` (zonder `.git`) als `vilpy-start.zip`.
3. GitHub-release `vX.Y.Z` aanmaken met die zip als asset. Sites updaten via de WordPress-updates.
