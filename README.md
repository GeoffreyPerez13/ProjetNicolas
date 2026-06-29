# Projet Nico — Site Vitrine Artiste

Site vitrine de présentation pour **Nico**, artiste peintre/plasticien.  
Inspiré de [camillebernard.org](https://www.camillebernard.org/index) — design minimaliste, élégant, centré sur les œuvres.

---

## Table des matières

1. [Installation](#installation)
2. [Structure du projet](#structure-du-projet)
3. [Architecture technique](#architecture-technique)
4. [Fonctionnalités — Site public](#fonctionnalités--site-public)
5. [Fonctionnalités — Administration](#fonctionnalités--administration)
6. [Responsive & Accessibilité](#responsive--accessibilité)
7. [Personnalisation](#personnalisation)
8. [Guide d'utilisation](#guide-dutilisation)
9. [Sécurité](#sécurité)
10. [URLs](#urls)

---

## Installation

### Prérequis

| Composant | Version requise |
|-----------|----------------|
| PHP | 7.4+ (testé avec 8.3) |
| MySQL | 5.7+ |
| Apache | 2.4+ avec `mod_rewrite` |
| Serveur local | WAMP, XAMPP, MAMP ou équivalent |

### Étapes d'installation

1. **Copier** le dossier du projet dans votre répertoire web :
   ```
   C:\wamp64\www\ProjetNico\
   ```

2. **Démarrer** WAMP (Apache + MySQL doivent être actifs)

3. **Exécuter le script d'installation** dans votre navigateur :
   ```
   http://localhost/ProjetNico/setup.php
   ```
   Ce script :
   - Crée la base de données `projet_nico`
   - Crée toutes les tables nécessaires
   - Insère les données par défaut (settings, footer, réseaux sociaux)
   - Crée l'utilisateur admin
   - Crée les dossiers d'upload

4. **Supprimer `setup.php`** après l'installation (sécurité)

### Identifiants admin par défaut

| Champ | Valeur |
|-------|--------|
| Utilisateur | `admin` |
| Mot de passe | `admin123` |

> ⚠️ **Changez le mot de passe** immédiatement après la première connexion via Admin > Mon profil.

---

## Structure du projet

```
ProjetNico/
├── index.php                   # Page d'accueil
├── 404.php                     # Page d'erreur 404 personnalisée
├── setup.php                   # Script d'installation (à supprimer)
├── .htaccess                   # Config Apache (sécurité, 404, cache, compression)
├── README.md                   # Ce fichier
│
├── config/
│   ├── database.php            # Connexion PDO à la BDD
│   └── init.sql                # Schéma SQL complet
│
├── includes/
│   ├── functions.php           # Fonctions utilitaires (requêtes, upload, slug, etc.)
│   ├── header.php              # En-tête HTML (meta SEO, nav, page loader)
│   └── footer.php              # Pied de page (social, lightbox, back-to-top)
│
├── pages/
│   ├── biographie.php          # Page biographie de l'artiste
│   ├── categorie.php           # Affichage d'une catégorie d'œuvres
│   ├── contact.php             # Formulaire de contact
│   ├── exposition.php          # Page d'une exposition
│   └── timeline.php            # Parcours / CV chronologique
│
├── admin/
│   ├── index.php               # Tableau de bord (statistiques)
│   ├── login.php               # Connexion admin
│   ├── logout.php              # Déconnexion
│   ├── profile.php             # Changement de mot de passe
│   ├── categories.php          # CRUD catégories
│   ├── oeuvres.php             # CRUD œuvres
│   ├── expositions.php         # CRUD expositions
│   ├── banner.php              # CRUD slides de bannière
│   ├── timeline.php            # CRUD événements timeline
│   ├── settings.php            # Paramètres (couleurs, typo, bio)
│   ├── footer.php              # Personnalisation du footer
│   ├── social.php              # Gestion des réseaux sociaux
│   └── includes/
│       └── sidebar.php         # Navigation latérale admin
│
├── assets/
│   ├── css/
│   │   ├── style.css           # Styles du site public (~1800 lignes)
│   │   └── admin.css           # Styles du panneau admin
│   └── js/
│       └── main.js             # JavaScript (slider, lightbox, animations, mobile)
│
└── uploads/                    # Dossier des images uploadées
    ├── .htaccess               # Bloque l'exécution PHP dans les uploads
    ├── oeuvres/                # Images des œuvres
    ├── categories/             # Images de couverture des catégories
    ├── expositions/            # Images de couverture des expositions
    ├── banner/                 # Images du slider d'accueil
    └── bio/                    # Photo de biographie
```

---

## Architecture technique

### Stack

- **Backend** : PHP 8 natif (pas de framework)
- **Base de données** : MySQL via PDO (requêtes préparées)
- **Frontend** : HTML5, CSS3 (variables CSS), JavaScript vanilla (ES6+)
- **Polices** : Google Fonts (dynamique via admin)
- **Pas de dépendances npm/composer** — tout est autonome

### Base de données (`projet_nico`)

| Table | Description |
|-------|-------------|
| `admin_users` | Comptes administrateur |
| `settings` | Paramètres du site (nom, couleurs, typo, bio) |
| `categories` | Catégories d'œuvres |
| `oeuvres` | Œuvres (images, technique, dimensions, année) |
| `expositions` | Expositions (dates, lieu, œuvres liées) |
| `exposition_oeuvres` | Table pivot exposition ↔ œuvres |
| `timeline_events` | Événements du parcours/CV |
| `social_links` | Liens réseaux sociaux |
| `footer_settings` | Configuration du footer |
| `banner_slides` | Slides de la bannière d'accueil |

### Patterns utilisés

- **Sessions PHP** pour l'authentification admin
- **Flash messages** pour le feedback utilisateur
- **Slug automatique** pour les URLs propres
- **Upload sécurisé** avec validation mime/extension/taille
- **Fonctions utilitaires** centralisées dans `functions.php`
- **Variables CSS dynamiques** injectées par les paramètres admin

---

## Fonctionnalités — Site public

### Navigation

- **Header fixe** avec hide/show au scroll (disparaît en scroll down, réapparaît en scroll up)
- **Menu responsive** : slide-in depuis la droite sur mobile
- **Dropdown dynamique** des catégories dans le menu
- **Page loader** élégant au chargement

### Page d'accueil

- **Bannière slider** : images plein écran avec overlay dégradé, titre/sous-titre
  - Auto-play (5s), navigation par points
  - **Swipe tactile** sur mobile
- **Grille des expositions** : cartes avec image, titre, date, lieu
- **Grille des catégories** : cartes avec image et nom
- **Fade-in au scroll** : animations d'apparition progressives

### Pages Œuvres (par catégorie)

- **5 types de mosaïques** configurables par catégorie :
  - `grid` — Grille régulière 3 colonnes
  - `masonry` — Colonnes avec hauteurs variables
  - `fullwidth` — Images pleine largeur empilées
  - `alternating` — Alternance image/texte (gauche/droite)
  - `mosaic` — Grille complexe avec éléments mis en avant
- **Lightbox** : vue plein écran de l'œuvre avec :
  - Navigation prev/next (flèches)
  - Compteur (ex: "3 / 12")
  - Swipe tactile
  - Clavier : ← → pour naviguer, Escape pour fermer
- **Scroll arrows** : boutons flottants pour aller en haut/bas de page

### Pages Expositions

- En-tête : titre, dates, lieu, description
- Grille des œuvres associées (même système de mosaïques)
- **Bouton "Exposition suivante"** : navigation fluide style musée

### Biographie

- Layout 2 colonnes (image sticky + texte) sur desktop
- Empilé sur mobile
- Contenu éditable depuis l'admin

### Parcours / Timeline

- Affichage chronologique vertical avec :
  - Année, titre, lieu, type d'événement, description
  - Ligne verticale décorative et points
  - Regroupement par type (exposition, formation, prix, résidence, publication)

### Contact

- Formulaire : nom, email, sujet (optionnel), message
- Validation côté serveur
- Messages de succès/erreur

### Footer

- Nom de l'artiste + atelier
- Icônes réseaux sociaux (liens dynamiques)
- Copyright personnalisable
- Crédit développeur (optionnel)
- HTML personnalisé

### UX & Animations

- **Back-to-top** : bouton flottant rond après 400px de scroll
- **Fade-in** : animation d'apparition au scroll (IntersectionObserver)
- **Hover effects** : zoom doux sur les images, underline sur les liens
- **Transitions douces** : tous les éléments interactifs sont animés

---

## Fonctionnalités — Administration

### Tableau de bord

- Statistiques en temps réel : nombre de catégories, œuvres, expositions, événements, slides
- Actions rapides vers les pages principales

### Gestion des catégories

- Créer / Modifier / Supprimer
- Champ : nom, description, image de couverture, ordre d'affichage, actif/inactif
- **Choix visuel du layout** de mosaïque (avec preview des 5 layouts)
- Génération automatique du slug

### Gestion des œuvres

- Créer / Modifier / Supprimer
- Champs : titre, description, image, catégorie, année, dimensions, technique, ordre
- Upload d'image avec prévisualisation
- Association à une catégorie

### Gestion des expositions

- Créer / Modifier / Supprimer
- Champs : titre, description, image de couverture, dates, lieu, layout, featured, ordre
- **Sélection multiple d'œuvres** à associer (checkboxes)
- Choix du layout de mosaïque

### Gestion de la bannière

- Créer / Modifier / Supprimer des slides
- Champs : image, titre, sous-titre, lien (optionnel), ordre, actif/inactif

### Gestion du parcours (Timeline)

- Créer / Modifier / Supprimer des événements
- Champs : année, titre, description, lieu, type d'événement
- Types : exposition, formation, prix, résidence, publication, autre

### Paramètres du site

- **Identité** : nom du site, prénom/nom de l'artiste, nom de l'atelier
- **Couleurs** (4) : primaire (texte), secondaire (fond header/footer), accent, fond
  - Color picker + champ hexadécimal
- **Typographie** : police des titres + police du corps (20 Google Fonts disponibles)
- **Biographie** : photo + texte long

### Personnalisation du footer

- Texte de copyright
- Afficher/masquer le nom de l'atelier
- Afficher/masquer les réseaux sociaux
- Nom + URL du développeur
- HTML personnalisé libre

### Réseaux sociaux

- Ajouter / Modifier / Supprimer des liens
- Plateformes : Instagram, Facebook, Twitter, YouTube, LinkedIn, TikTok, Behance, Autre
- Ordre d'affichage, actif/inactif

### Profil admin

- Changement de mot de passe sécurisé (vérification de l'ancien)

---

## Responsive & Accessibilité

### Breakpoints CSS

| Largeur | Cible | Adaptations principales |
|---------|-------|------------------------|
| > 1200px | Desktop large | Layout complet |
| ≤ 1200px | Petit desktop | Navigation resserrée |
| ≤ 1024px | Tablette paysage | Grilles 2 colonnes, bio empilée |
| ≤ 768px | Tablette / Mobile | Menu hamburger, footer centré, lightbox mobile |
| ≤ 480px | Petit smartphone | Typo réduite, 1 colonne, boutons 100% |
| ≤ 360px | Très petit écran | Ultra-compact |

### Fonctionnalités responsive

- **Menu mobile** : slide-in depuis la droite avec overlay
- **Admin mobile** : sidebar cachée + bouton hamburger flottant
- **Tables admin** : scroll horizontal sur mobile
- **Touch devices** : hover effects remplacés par overlays permanents
- **Swipe** : navigation tactile dans le slider et la lightbox
- **`100dvh`** : hauteurs adaptées aux barres de navigation mobiles

### Accessibilité

- **`prefers-reduced-motion`** : désactive les animations si l'utilisateur le préfère
- **`aria-label`** sur tous les boutons sans texte
- **Contraste** : personnalisable via les couleurs admin
- **Focus visible** : outlines sur les éléments focusables
- **Keyboard navigation** : lightbox navigable au clavier

### Performance

- **Lazy loading** natif (`loading="lazy"`) sur les images sous le fold
- **Page loader** : masque le FOUC pendant le chargement
- **requestAnimationFrame** : scroll events optimisés
- **Compression gzip** activée via .htaccess
- **Cache navigateur** configuré pour les assets statiques

### Impression

- Header, footer, boutons flottants masqués automatiquement
- Contenu optimisé pour l'impression papier

---

## Personnalisation

### Couleurs (via Admin > Paramètres)

| Variable CSS | Rôle | Défaut |
|---|---|---|
| `--color-primary` | Texte principal, éléments forts | `#0a0a0a` |
| `--color-secondary` | Fond du header et footer | `#ffffff` |
| `--color-accent` | Texte secondaire, metadata | `#6b7280` |
| `--color-bg` | Fond de page | `#fafafa` |

### Typographies disponibles

**Titres (serif/display)** : Cormorant Garamond, Playfair Display, EB Garamond, Libre Baskerville, Lora, Merriweather, Spectral, DM Serif Display, Bodoni Moda

**Corps (sans-serif)** : Inter, Poppins, Montserrat, Raleway, Work Sans, DM Sans, Nunito Sans, Source Sans 3, Outfit, Space Grotesk, Josefin Sans

### Layouts de mosaïque

Chaque catégorie et exposition peut avoir son propre layout :

1. **Grid** — Grille régulière, toutes les images de même taille
2. **Masonry** — Style Pinterest, hauteurs variables
3. **Fullwidth** — Images pleine largeur, une par ligne
4. **Alternating** — Image à gauche + texte à droite, alternant
5. **Mosaic** — Mix de grandes et petites images

---

## Guide d'utilisation

### Première utilisation

1. Connectez-vous à l'admin : `http://localhost/ProjetNico/admin/`
2. **Changez votre mot de passe** (Mon profil)
3. **Paramètres** : configurez le nom, les couleurs, la typographie
4. **Créez des catégories** (ex: Peintures, Sculptures, Dessins)
5. **Ajoutez des œuvres** avec leurs images
6. **Créez des expositions** et associez-y des œuvres
7. **Ajoutez des slides** à la bannière d'accueil
8. **Remplissez la biographie** et le **parcours/timeline**
9. **Configurez le footer** et les **réseaux sociaux**

### Workflow typique pour ajouter une exposition

1. Admin > Œuvres > Créer les œuvres (avec images)
2. Admin > Expositions > Nouvelle exposition
3. Remplir titre, dates, lieu, description
4. Sélectionner les œuvres à inclure
5. Choisir le layout de mosaïque souhaité
6. L'exposition apparaît automatiquement sur la page d'accueil

### Formats d'images recommandés

| Usage | Format | Dimensions conseillées |
|-------|--------|----------------------|
| Bannière | JPG/WebP | 1920×1080 px |
| Œuvres | JPG/PNG | 1200×1600 px (portrait) ou 1600×1200 px (paysage) |
| Couvertures (catégories/expos) | JPG/WebP | 1200×900 px |
| Photo biographie | JPG | 800×1000 px |

> Taille max par fichier : **5 Mo**  
> Formats acceptés : JPG, JPEG, PNG, GIF, WebP

---

## Sécurité

- **Mot de passe** hashé avec `password_hash()` (bcrypt)
- **Sessions PHP** pour l'authentification admin
- **Requêtes préparées** (PDO) — protection contre les injections SQL
- **`htmlspecialchars()`** — protection contre le XSS
- **`.htaccess`** :
  - Bloque l'accès aux fichiers `.sql` et `.md`
  - Bloque l'exécution PHP dans `/uploads/`
  - Protège le dossier `/config/`
- **Upload sécurisé** : validation extension + mime type + taille
- **Page 404 personnalisée**

---

## URLs

| Page | URL |
|------|-----|
| Site public | `http://localhost/ProjetNico/` |
| Administration | `http://localhost/ProjetNico/admin/` |
| Biographie | `http://localhost/ProjetNico/pages/biographie.php` |
| Parcours | `http://localhost/ProjetNico/pages/timeline.php` |
| Contact | `http://localhost/ProjetNico/pages/contact.php` |
| Catégorie | `http://localhost/ProjetNico/pages/categorie.php?slug=nom-categorie` |
| Exposition | `http://localhost/ProjetNico/pages/exposition.php?slug=nom-expo` |

---

## Crédits

- **Design** : Inspiré de [camillebernard.org](https://www.camillebernard.org)
- **Icônes** : SVG inline (style Lucide)
- **Polices** : Google Fonts
- **Développement** : Projet sur mesure en PHP/MySQL/CSS/JS vanilla

---

*Dernière mise à jour : Juin 2026*
