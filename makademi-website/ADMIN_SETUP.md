# Makademi Admin Portal — Hostinger Setup

This site now includes a password-protected admin area where you can add, edit, and delete training programs and gallery photos **without ever editing code or re-uploading the zip**.

You only have to do this setup **once**. After that, day-to-day changes are made in your browser at `https://yourdomain.com/admin/`.

---

## What you need

- Your Hostinger Business plan (or any plan that supports **PHP 8.1+ and MySQL**)
- The `makademi-website.zip` file
- 15 minutes

---

## Step 1 — Upload the site files

1. Log into **hPanel** (Hostinger's control panel).
2. Go to **Files → File Manager**.
3. Open the `public_html` folder (the root of your domain).
4. **Delete any old Makademi files** that are already in `public_html` (back them up first if you want).
5. Click **Upload** and select `makademi-website.zip`.
6. Right-click the uploaded zip and choose **Extract**. Extract into `public_html` directly (so the files end up at `public_html/index.html`, `public_html/courses.php`, etc., **not** inside a `makademi-website` subfolder).
7. Delete the now-empty zip file.

---

## Step 2 — Create the MySQL database

1. In hPanel, go to **Databases → MySQL Databases**.
2. Click **Create Database** and enter:
   - Database name: anything you like, e.g. `makademi`
   - Username: anything you like, e.g. `makademi_admin`
   - Password: click **Generate** and **save it somewhere safe** — you'll need it in Step 3.
3. Click **Create**.
4. Hostinger will show you the **full names** with your account prefix, for example:
   - DB name: `u123456789_makademi`
   - Username: `u123456789_admin`
   - Host: usually `localhost`
5. **Copy these three values** (DB name, username, password) — you'll paste them next.

---

## Step 3 — Configure the site to talk to the database

1. Back in **File Manager**, open `public_html`.
2. Find **`config.example.php`** in the root.
3. Right-click → **Copy** → paste it into the `includes/` folder.
4. Open `public_html/includes/` and **rename** the new copy to **`config.php`**.
5. Right-click `config.php` → **Edit** and change three things:

   **(a)** Database credentials from Step 2:

   ```php
   'db_driver'  => 'mysql',
   'db_host'    => 'localhost',
   'db_name'    => 'u123456789_makademi',     // your DB name from Step 2
   'db_user'    => 'u123456789_admin',        // your DB username from Step 2
   'db_pass'    => 'PASTE_YOUR_PASSWORD_HERE',// your DB password from Step 2
   'db_port'    => 3306,
   'db_charset' => 'utf8mb4',
   ```

   **(b)** A long random `app_secret`. This is **mandatory** — the admin setup page refuses to run while the placeholder is still in place. Generate one using any of these:
   - In a terminal: `openssl rand -hex 32`
   - Online: any reputable "random hex string generator" (64 hex characters)
   - Or just mash 64+ random letters/numbers into the string

   ```php
   'app_secret' => 'paste-64-or-more-random-hex-characters-here-do-not-share-this',
   ```

   **Save this value somewhere safe** — you'll paste it once on the next step.

6. Click **Save**.

---

## Step 4 — Import the database schema and data

1. In hPanel, go to **Databases → phpMyAdmin** and click **Enter phpMyAdmin** next to your `makademi` database.
2. In phpMyAdmin, click your database name in the left sidebar.
3. Click the **Import** tab at the top.
4. Click **Choose File** and select `db/setup.sql` from your downloaded `makademi-website.zip` (or download it from File Manager first — it lives at `public_html/db/setup.sql`).
5. Scroll down and click **Import** (or **Go**).
6. You should see a green success message and tables in the left sidebar:
   `admin_users`, `categories`, `programs`, `gallery_categories`, `gallery_images`.

---

## Step 5 — Create your admin account

1. Open your site in a browser at:
   ```
   https://yourdomain.com/admin/setup-account.php
   ```
2. The page will ask for three things:
   - **Setup secret** — paste the **exact `app_secret` value** you put in `includes/config.php` in Step 3(b). This stops anyone else on the internet from creating your admin account before you do.
   - **Username** — choose anything (3-64 letters/numbers/dot/dash/underscore).
   - **Password** — at least 12 characters, the longer and more random the better.
3. Click **Create admin account**.
4. You'll see a confirmation. The page will refuse to run a second time — that's the security feature, no need to delete anything.

> **Belt-and-braces (recommended):** in File Manager, delete `public_html/admin/setup-account.php` after this step. Not required (the page self-disables on multiple layers), but it removes the file from the server entirely.

---

## Step 6 — Log in and start managing the site

Log in at:
```
https://yourdomain.com/admin/login.php
```

You'll land on a dashboard with two sections:

- **Programs** — add, edit, delete the 101 training courses. Set title, category, description, duration, location, optional detail-page URL, sort order, and "published" toggle.
- **Gallery** — create gallery categories (e.g. "Firefighter Training"), upload photos with captions, delete photos. Photos are saved to `assets/images/gallery/` automatically.

Public pages (`/courses.php`, `/gallery.php`) update **immediately** when you save — no re-upload, no waiting.

---

## Day-to-day usage

| What you want to do                  | Where                                     |
|--------------------------------------|-------------------------------------------|
| Add a new training program           | Admin → Programs → **+ New program**      |
| Hide a program temporarily           | Admin → Programs → edit → uncheck "Published" |
| Add a new gallery category           | Admin → Gallery → **+ New category**      |
| Upload a photo to a category         | Admin → Gallery → category card → **Upload photo** |
| Change a photo caption               | Admin → Gallery → caption field → **Save** |
| Delete a photo (file too)            | Admin → Gallery → photo → **Delete**      |
| Sign out                             | Top-right **Sign out** link               |

---

## Security notes

- All admin actions require login + a per-form CSRF token.
- Passwords are stored as bcrypt hashes (never plaintext).
- Login throttles after 5 failed attempts (60-second lockout per session).
- File uploads validate MIME type via `finfo` (jpg, png, webp, gif only) and reject files over 10 MB.
- Uploaded files get random hex names — original filenames are discarded.
- Sessions use `HttpOnly`, `Secure` (when on HTTPS), and `SameSite=Lax` cookies.

---

## Troubleshooting

**"Database connection failed"** — double-check the values in `includes/config.php` against Step 2. The keys must be exactly `db_host`, `db_name`, `db_user`, `db_pass`, `db_port`, `db_charset` (each prefixed with `db_`). The most common typo is forgetting the `u123456789_` prefix Hostinger adds to your DB name and username.

**"Setup is locked: the file includes/config.php still has the default app_secret placeholder"** — you forgot Step 3(b). Open `includes/config.php`, change `app_secret` from the placeholder to a long random string, save, and reload the setup page.

**"The setup secret does not match the app_secret in includes/config.php"** — the value you pasted into the form's "Setup secret" field doesn't match the `app_secret` in your config file. Open `includes/config.php` and copy the value between the quotes exactly (no extra spaces, no quotes themselves).

**"Setup has already been completed"** on `/admin/setup-account.php` — that's expected after Step 5. To create a fresh admin account, delete the row in the `admin_users` table via phpMyAdmin, **and** delete `public_html/admin/.installed` if it exists, then re-visit the page.

**Forgot your password** — open phpMyAdmin → `admin_users` table → delete the row → also delete `public_html/admin/.installed` → revisit `/admin/setup-account.php`.

**Uploaded photo doesn't appear** — make sure `public_html/assets/images/gallery/` exists and is writable (chmod 755 in File Manager). The folder is created automatically on first upload, but some hosts require it to exist beforehand.

**"500 Internal Server Error"** — check Hostinger's PHP error log (hPanel → Advanced → Error Logs). Most often it's a typo in `includes/config.php`.

---

## Updating the site

Need to change the homepage text, "About" page, or the contact form? Those pages are still plain HTML — edit them in File Manager and save. The admin portal only manages programs and gallery (the parts that change frequently).

If you ever want to rebuild from a fresh zip without losing your data:
1. Export your DB from phpMyAdmin (Export tab → SQL → Go) as a backup.
2. Upload + extract the new zip.
3. Re-do Step 3 (`includes/config.php`) — your existing `config.php` will be overwritten by the new zip. Keep your DB credentials handy.
4. Your data and admin account are still in MySQL — no other action needed.
