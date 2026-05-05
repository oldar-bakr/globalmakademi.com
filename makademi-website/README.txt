MAKADEMI TRAINING HUB - WEBSITE + ADMIN PORTAL
================================================

This is a PHP + MySQL website. The public pages (home, about, contact)
are plain HTML. The training programs catalog and the photo gallery
are powered by PHP + a database, and you can manage them through a
password-protected admin area in your browser.

You do NOT need to edit code or re-upload files to add a course or a
photo. Once it's set up, everything happens in your browser.


HOSTING ON HOSTINGER
--------------------

Full step-by-step setup guide is in:

    ADMIN_SETUP.md

Read that file. It walks through:

  1. Upload + extract the zip into public_html
  2. Create a MySQL database in hPanel
  3. Edit includes/config.php with your DB credentials
  4. Import db/setup.sql via phpMyAdmin (loads schema + 101 courses + gallery)
  5. Visit /admin/setup-account.php once to create your admin user
  6. Log in at /admin/login.php and start managing courses & gallery

You only do steps 1-5 once. After that, everything is browser-based.


REQUIREMENTS
------------

- PHP 8.1 or newer (Hostinger Business plan default is fine)
- MySQL 5.7+ or MariaDB 10.3+ (Hostinger default)
- A folder where PHP can write to assets/images/gallery/ (for photo uploads)


FILE STRUCTURE
--------------

makademi-website/
  index.html              Home page (static)
  courses.php             Programs catalog (DB-driven, search + filter)
  gallery.php             Photo gallery (DB-driven, lightbox)
  about.html              About, clients, partners, accreditations
  contact.html            Contact form + info
  404.html                Custom 404 page
  favicon.ico, *.png      Favicons / app icons
  config.example.php      Template — copy to includes/config.php and fill in DB creds

  admin/                  Password-protected admin portal
    setup-account.php     One-time: create the admin user (self-disables)
    login.php             Sign-in form
    logout.php            Sign-out
    index.php             Dashboard
    programs.php          Manage training programs (add / edit / delete)
    gallery.php           Manage gallery categories + photos (upload / delete)

  includes/               PHP infrastructure (do not modify unless you know PHP)
    config.php            **YOU CREATE THIS** from config.example.php
    db.php                PDO connection
    auth.php              Session + login helpers
    csrf.php              CSRF token generator/checker
    helpers.php           Escape / redirect / category helpers
    partials/             Shared header/footer/lightbox HTML

  db/
    setup.sql             **MySQL** schema + seed data — import via phpMyAdmin
    setup.sqlite.sql      SQLite version (used only for local development)

  assets/
    css/                  Site styles + admin styles
    js/                   Public site JavaScript
    images/               Logos, hero images, course images
      gallery/            **Auto-populated by admin uploads**

  courses/
    firefighting-joiff.html       Detail page for the JOIFF firefighting course
    electric-hybrid-vehicle.html  Detail page for the EV course


WHAT THE ADMIN PORTAL CAN DO
----------------------------

Programs (admin/programs.php):
  - Add a new training course (title, category, description, duration,
    location, optional detail URL, sort order, published toggle)
  - Edit any field of an existing course
  - Delete a course
  - Search by title/keyword
  - Filter by category
  - Toggle "published" to temporarily hide a course from the public site

Gallery (admin/gallery.php):
  - Create new gallery categories (with a colored badge)
  - Upload photos (jpg, png, webp, gif up to 10 MB) with captions
  - Edit captions inline
  - Delete photos (removes both the DB row AND the file from disk)
  - Delete entire categories


CONTACT FORM
------------

The contact form on contact.html submits to FormSubmit.co (a free service)
and delivers each inquiry to info@globalmakademi.com.

IMPORTANT: FormSubmit needs a one-time activation click on the inbox
before any mail starts flowing. Setup guide:

    CONTACT_FORM_SETUP.md


CUSTOM 404 PAGE
---------------

In hPanel, go to Advanced > Error Pages and set 404 to /404.html.
Or create a .htaccess file in public_html with:

    ErrorDocument 404 /404.html


FONTS
-----

Google Fonts (Inter + DM Sans), loaded from Google's CDN. No fonts are
installed locally.


CHANGING DESTINATION EMAIL OR HOMEPAGE TEXT
-------------------------------------------

The home, about, and contact pages are still plain HTML. Edit them in
Hostinger's File Manager and save. Only the programs catalog and the
gallery are database-driven.

To change the contact form's destination email, edit contact.html, find:

    action="https://formsubmit.co/info@globalmakademi.com"

and replace the address. Then redo the FormSubmit activation step
(see CONTACT_FORM_SETUP.md) for the new address.
