MAKADEMI TRAINING HUB - STATIC WEBSITE
======================================

This is a plain HTML/CSS/JavaScript website. No build step is needed.
Upload these files directly to any web host.


HOSTING ON HOSTINGER (FILE MANAGER)
------------------------------------

1. Log into your Hostinger control panel (hPanel).
2. Go to Files > File Manager.
3. Navigate to the public_html folder (or your domain's root folder).
4. Upload ALL contents of this folder into public_html:
   - index.html
   - courses.html
   - about.html
   - contact.html
   - 404.html
   - favicon.svg
   - assets/ (entire folder)
   - courses/ (entire folder)

   You can drag-and-drop the whole folder, or upload the zip and
   use File Manager's "Extract" feature.

5. Your site should now be live at your domain!


FILE STRUCTURE
--------------

makademi-website/
  index.html              Home page
  courses.html            Course catalog (101 programs, search + filter)
  about.html              About, clients, partners, accreditations
  contact.html            Contact form + info
  404.html                Custom 404 page
  favicon.svg             Browser tab icon
  assets/
    css/styles.css         All styles (navy #00234B + gold #D4AF37 palette)
    js/main.js             Mobile nav, accordion, search/filter, counters
    images/                Logo, hero backgrounds, course images
  courses/
    firefighting-joiff.html       JOIFF firefighting detail page
    electric-hybrid-vehicle.html  EV technology detail page


EDITING PAGES
--------------

Each .html file is self-contained. Open any file in a text editor
(VS Code, Notepad++, Sublime Text) to modify:

- Text content: search for the text you want to change
- Colors: edit assets/css/styles.css — look for --navy, --gold variables
- Add new courses: duplicate a course card in courses.html
- Add new pages: copy an existing .html file and modify the content
- Contact form: edit the form action in contact.html
  (currently set to FormSubmit; see comment in the HTML)


CONTACT FORM
-------------

The contact form on contact.html submits to FormSubmit.co (a free service)
and delivers each inquiry to info@globalmakademi.com with a clean subject
line, a Reply-To set to the visitor's address, and every field laid out
as a readable table.

IMPORTANT: FormSubmit needs a one-time activation click on the inbox
before any mail starts flowing. The full step-by-step guide is in:

  CONTACT_FORM_SETUP.md

Read that file before going live.

To change the destination email later:
  - Edit contact.html, find:
      action="https://formsubmit.co/info@globalmakademi.com"
    and replace the address.
  - Re-upload contact.html and redo the FormSubmit activation step
    (described in CONTACT_FORM_SETUP.md) for the new address.


CUSTOM 404 PAGE
----------------

To use 404.html on Hostinger:
1. In hPanel, go to Advanced > Error Pages
2. Set 404 error to point to /404.html
3. Or create a .htaccess file in public_html with:
   ErrorDocument 404 /404.html


FONTS
------

The site uses Google Fonts (Inter + DM Sans), loaded from Google's CDN.
No fonts are installed locally — they load automatically with an
internet connection.


EXTERNAL IMAGES
----------------

Partner/client logos load from their original external URLs.
If any logo fails to load, a text fallback is shown automatically.
To replace with local images: save the image into assets/images/
and update the src attribute in the HTML.
