# Contact Form Setup — One-Time Activation

The contact form on `contact.html` sends inquiries to **info@globalmakademi.com**
through a free service called **FormSubmit** (https://formsubmit.co). No
account, signup, or API key is required, but the inbox does need to confirm
itself once before any mail starts flowing.

This guide takes about 5 minutes the first time, then never needs to be
touched again unless the destination email changes.

> **Heads up — destination is hidden behind an alias.**
> The form does **not** post to `formsubmit.co/info@globalmakademi.com`
> directly. It posts to a random-looking alias FormSubmit issued for that
> inbox: `formsubmit.co/4a25594be38072bdab39513b617f5846`. The alias points
> at the same inbox but keeps the email address out of the public HTML so
> spam-bots can't scrape it. **Do not change this URL** unless you also
> redo the activation step below for the new address.

---

## Step 1 — Upload the website

Upload the contents of `makademi-website/` (or unpack `makademi-website.zip`)
into your Hostinger `public_html` folder so the site is live at
`https://globalmakademi.com`.

The form **must** be submitted from the live domain — not from a local
preview — for FormSubmit to consider it valid.

---

## Step 2 — Send the very first test inquiry

1. Open https://globalmakademi.com/contact.html in a browser.
2. Fill in every field with realistic test data. Use a **real** email
   address you can access (e.g. your own personal email) in the
   *Business Email* field — FormSubmit echoes it back so you can verify
   the reply-to is set correctly.
3. Click **Submit Inquiry**.
4. The page will show the green "Inquiry Sent Successfully" panel.

Behind the scenes, FormSubmit has now received the submission but will
**not yet forward it** to `info@globalmakademi.com`. Instead, it sends
that inbox a one-time activation email.

---

## Step 3 — Confirm the FormSubmit activation email

1. Sign into the **info@globalmakademi.com** mailbox.
2. Look for a message from **`no-reply@formsubmit.co`** that starts with
   *"Thank you for using FormSubmit! You're only one step away..."*.
3. **Check the spam / junk folder** if it's not in the inbox after a
   couple of minutes.
4. Open the email and click the **Activate Form** button.
5. You should land on a FormSubmit page that says the form is now
   confirmed.

> The same email also gives you a "random-like string" (a long alias)
> and tells you to use it in place of the naked email in the form's
> action attribute. **This is already done** — `contact.html` ships with
> the alias `4a25594be38072bdab39513b617f5846` baked into the form's
> action URL. No further code change is required.

### Whitelist FormSubmit so future mail does not go to spam

While you have the inbox open, mark `no-reply@formsubmit.co` as a
trusted sender so live inquiries never get filtered:

- **Hostinger / Webmail (Roundcube):** open the message → ⋯ menu →
  **Mark as not spam**, then add the sender to the address book.
- **Gmail (if you forward to Gmail):** open the message →
  **Filter messages like this** → tick **Never send it to Spam**.
- **Outlook:** Right-click the message → **Junk** → **Never block
  sender**.

---

## Step 4 — Send a second inquiry to confirm end-to-end delivery

1. Go back to https://globalmakademi.com/contact.html.
2. Fill the form in again with realistic test content.
3. Submit it.
4. Within ~30 seconds, **info@globalmakademi.com** should receive an
   email that looks like this:

   - **From:** `no-reply@formsubmit.co`
   - **Subject:** `New inquiry from globalmakademi.com`
   - **Reply-To:** the email address the visitor typed into the form
     (so hitting *Reply* in your mail client goes straight back to them).
     This is set two ways for safety: a hidden `_replyto` field that JS
     fills in from the email input on submit, and FormSubmit's automatic
     detection of the lowercase `email` field as a fallback.
   - **Body:** a clean table with rows for *Full Name, Company, Email,
     Phone, Industry, Subject, Message*.

If the second inquiry arrives, the form is fully live. You're done.

---

## Troubleshooting

**"I never got the activation email."**
- Check the spam folder.
- Check that the form was submitted from the *real* domain
  (`https://globalmakademi.com`), not from a local preview or the
  Hostinger staging URL.
- Wait 5 minutes and refresh the inbox; FormSubmit's first send can
  occasionally be slow.

**"The form shows the success panel but no email arrives."**
- This usually means FormSubmit is still waiting for activation. Repeat
  Step 3 above.
- Once activated, every following submission is forwarded automatically.

**"I want to change the destination email."**
- Edit `contact.html`, find the line
  `action="https://formsubmit.co/4a25594be38072bdab39513b617f5846"` and
  replace the alias with the new address (e.g.
  `action="https://formsubmit.co/new@example.com"`).
- Re-upload `contact.html`, then redo Step 2 + Step 3 — the new address
  needs its own one-time activation.
- The activation email for the new inbox will give you a fresh alias.
  Once you have it, swap the bare email back out for that alias to keep
  the destination hidden in the public HTML.

**"We're suddenly getting spam through the form."**
- A hidden honeypot field already drops obvious bots.
- If real spam slips through, open `contact.html` and change
  `<input type="hidden" name="_captcha" value="false">` to
  `value="true"`. FormSubmit will then add a CAPTCHA challenge after
  submit. Re-upload the file.

**"FormSubmit went down / we want to stop depending on a third party."**
- Replace the `<form action="...">` URL with a Hostinger-hosted PHP
  `mail()` script or another provider. The rest of the form (fields,
  JS handler, success panel) does not need to change.
