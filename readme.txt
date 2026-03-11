=== Vilpy ===
Tags: vilpy
Requires at least: 4.7
Tested up to: 6.0
Requires PHP: 7.0

Vilpy is de drijvende kracht achter alle websites die gebouwd zijn door Vilpy.

== Description ==
Vilpy is de drijvende kracht achter alle websites die gebouwd zijn door Vilpy.

== Installation ==
Installeren, activeren en gaan met die banaan

== Changelog ==

= 0.0.67 =

Forced the dashboard welcome logo to a fixed width of 140px for uploaded SVG logos.

= 0.0.66 =

Switched plugin updates from the GitHub API to a static update.json feed to avoid 403 errors.

= 0.0.65 =

Reduced the dashboard welcome logo width from 240px to 140px.

= 0.0.64 =

Sanitized the welcome text editor to remove TinyMCE bookmark markup from saved content.

= 0.0.63 =

Replaced both plugin banner assets with the new "plugin update screenshot" artwork.

= 0.0.62 =

Added a retina plugin banner so WordPress no longer falls back to stale banner artwork.

= 0.0.61 =

Replaced the plugin details banner with the current Vilpy login background.

= 0.0.60 =

Forced Bricolage Grotesque inline on the dashboard welcome heading and text.

= 0.0.59 =

Translated the Vilpy settings labels and section copy to Dutch.

= 0.0.58 =

Updated Vilpy admin typography to Bricolage Grotesque on settings and dashboard screens.

= 0.0.57 =

Switched the GitHub repository to public and removed the updater token requirement.

= 0.0.56 =

Added a GitHub updater token setting for private repository updates.

= 0.0.55 =

Updated dashboard welcome branding, set Bricolage Grotesque, and fixed login logo sizing.

= 0.0.54 =

Updated Vilpy login background asset and split frontend/backend logo defaults.

= 0.0.53 =

Release date: ?

* Toevoegingen 
    - Je kunt nu Google Tag Manager, Google Analytics en Google Search Console toevoegen via het nieuwe tabje tracking.
    - Je kunt nu een woocomerce datalayer aanzetten via het tabje tracking.
    - Standaard login url aangepast naar /vilpy tenzei anders ingesteld in de instellingen. Als perfmatters het aanpast dan neemt hij die als leidend.

* Fixes
    - Functie isLoginPassThrough aangepast tbv session timout ($hits_wp_login = (strpos($uri, 'wp-login.php') !== false); is een te strenge controle, staat nu verder naar onder)

= 0.0.52 =

Release Date: Januari 16th, 2024

* Fixes
    - De CSS code voor de elementor selector pijltjes werkte niet meer goed i.v.m. elementor updates. Dat hebben we opgelost.

= 0.0.51 =

Release Date: December 18th, 2023

* Toevoegingen
    - Je kunt nu post types aangeven waarvan de post cloudflare cache geleegd moet worden op update.
    - Je kunt nu post types aangeven waarvan de post litespeed cache geleegd moet worden op update.

* Aanpassingen
    - De vilpy acties knop is er nu alleen voor beheerders.

= 0.0.50 =

Release Date: December 15th, 2023

* Toevoegingen
    - Je kunt nu cloudflare en litespeed cache legen via de admin bar, je kunt dit instellen bij algemene instellingen. (Handig voor klanten)
    - Je kunt nu Shoptimizer & Shoptimizer child installeren via Vilpy.

* Aanpassingen
    - WP Mail SMTP is vervangen voor FluentSMTP.

= 0.0.49 =

Release Date: September 8th, 2023

* Fixes
    - Het vilpy welkom scherm had een probleem met CookieYes. Dat hebben we in deze update opgelost.

= 0.0.48 =

Release Date: Jan 20th, 2023

* Fixes
    - Nieuwe elementor update heeft de pijltjes aangepast. Dus hebben we deze bug opgelost.

= 0.0.47 =

Release Date: Nov 23th, 2022

* Fixes
    - Vilpy klant kan nu vanuit de frontend 'pagina bewerken' als zij ingelogd zijn.

= 0.0.46 =

Release Date: Nov 8th, 2022

* Fixes
    - Wat aanpassingen in het CSS bestandje voor het menu, zat een kleine styling fout in.

= 0.0.45 =

Release Date: Nov 8th, 2022

* Fixes
    - Wat aanpassingen in het CSS bestandje voor het menu, zat een kleine styling fout in.

= 0.0.44 =

Release Date: Oct 18th, 2022

* Toevoegingen
    - Er is nu een nieuwe optie voor het uitzetten van de icons.css ook wordt er hierbij extra gecontroleerd op de aanwezigheid van de Elementor plugin.

= 0.0.43 =

Release Date: Sept 28th, 2022

* Toevoegingen
    - Extra tab met willekeurige instellingen. Showcase modus en scroll snap.

* Fixes
    - Het weghalen van menu items voor 'Vilpy Klant' zou nu goed moeten werken. De vilpy klant rol is nu gebaseerd op administrator.

= 0.0.42 =

Release Date: Sept 14th, 2022

* Toevoegingen
    - Welkomstbericht is nu aanpasbaar

* Fixes
    - Standaard grootte van het logo is nu 150px

= 0.0.41 =

Release Date: July 29th, 2022

* Fixes
    - Logo op inlogpagina wordt nu weer goed weergegeven
    - Fixes voor het weghalen van Yoast en Contact form 7

= 0.0.40 =

Release Date: June 24th, 2022

* Fixes
    - Probleem opgelost waarbij je de categorieen taxonomy van een custom post type niet kon weghalen.
    - Patch toegevoegd voor plugins met rare capablities die wel in het menu verschijnen maar niet de juiste capablitie hebben.

= 0.0.39 =

Release Date: June 22th, 2022

* Fixes
    - Bug fix voor nieuwe versie van elementor

= 0.0.38 =

Release Date: May 27th, 2022

* Fixes
    - Wordpress 6 introduceerde wat nieuwe kleine foutjes. Deze zijn nu opgelost.

= 0.0.37 =

Release Date: May 20th, 2022

* Fixes
    - Redirect bij profiel aanpassen is nu alleen bij vilpy klant

= 0.0.36 =

Release Date: May 18th, 2022

* Toevoegingen
    - Welkomstbericht neemt nu de styling over van de klant instellingen

* Fixes
    - Probleem opgelost waarbij de header bij post types werd weggehaald.

= 0.0.35 =

Release Date: May 17th, 2022

* Fixes
    - Klein foutje in de vilpy klant editor opgelost.

= 0.0.34 =

Release Date: May 10th, 2022

* Toevoegingen
    - Vilpy klant rol wordt nu automatisch geupdate, reactivatie is niet meer nodig.
    - Wordpress logo is nu ook verborgen voor een klant.

= 0.0.33 =

Release Date: May 6th, 2022

* Toevoegingen
    - Het menu van de Vilpy klant rol kan nu aangepast worden!
    - Het welkomstbericht heeft een nieuw sausje gekregen.
    - Iconenlijsten worden nu automatisch goed uitgelijnd.

= 0.0.32 =

Release Date: April 8th, 2022

* Toevoegingen
    - Je kunt nu ook thema's installeren ;)

* Fixes
    - De testomgeving opzetter heeft nu een eigen tabje

= 0.0.30 =

Release Date: March 4th, 2022

* Toevoegingen
    - De antibas popup, zodat Bas de website niet perongeluk leeghaalt.

= 0.0.29 =

Release Date: February 21th, 2022

* Toevoegingen
    - Anywhere elementor wordt nu weggehaald voor vilpyklant

= 0.0.27 =

Release Date: February 3th, 2022

* Toevoegingen
    - Scroll snap support voor op mobiel is nu beschikbaar.

* Opgelost
    - Support for WordPress 5.9.

= 0.0.26 =

Release Date: January 24th, 2022

* Opgelost
    - Classname veranderd zodat het goed werkt met andere plugins.

= 0.0.24 = 

Release Date: January 13th, 2022

* Toevoegingen
    - Elementor iconen worden nu standaard geinstalleerd op product activatie.
    - Je kunt nu een testomgeving opzetten met 1 druk op de knop.

* Opgelost
    - Vilpy kan nu gecombineerd worden met alle thema's
    - Wanneer Elementor niet is geinstalleerd zullen de Elementor functies ook niet draaien.

= 0.0.23 = 

Release Date: January 5th, 2022

* Toevoegingen
    - Het vilpy child thema wordt nu standaard geinstalleerd.

= 0.0.21 =

Release Date: December 13th, 2021

* Toevoegingen
        - Je kan nu een test omgeving HALLO aanmaken met 1 klik op de knop. Vilpy is de eerste plugin die je moet installeren. Als je op de testomgeving knop drukt:
        - Wordt alle standaard Wordpress content verwijderd, zoals comments, pagina\'s, blogs etc.
        - Wordt het standaard vilpy thema geinstalleerd op basis van je keuze voor een Webshop of een website.
        - Worden alle standaard WordPress instellingen aangepast: tijdzone is Amsterdam, Zoekmachine zichtbaarheid uit, avatars uit, blog pingback uit, permalinks op bericht naam.
