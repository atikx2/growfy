# Growfy Agency — Custom PHP Website (WordPress ছাড়া)

সম্পূর্ণ custom-coded agency website — **কোনো framework বা WordPress নেই**, pure PHP।
Full admin panel, order management, contact inbox, newsletter — সবকিছু সহ।

![PHP 8+](https://img.shields.io/badge/PHP-8.0%2B-8892BF) ![MySQL/SQLite](https://img.shields.io/badge/DB-MySQL%20%2F%20SQLite-4ade80) ![No Framework](https://img.shields.io/badge/Framework-None-f0abfc)

---

## ✨ ফিচারসমূহ

**ওয়েবসাইট (Frontend)**
- 🌗 **Dark/Light mode toggle** (navbar-এ) — visitor-এর পছন্দ মনে রাখে + system theme respect করে
- Premium dark UI — glassmorphism navbar, gradient hero, floating stat cards
- Animated counters, testimonial slider (drag/swipe), FAQ accordion, platform marquee
- **Order Now modal** — ভিজিটর সরাসরি service order/inquiry করতে পারবে
- Contact form + Newsletter subscription (AJAX + toast notification)
- WhatsApp floating button, scroll animations, full responsive (mobile menu)
- SEO ready — meta tags, Open Graph, JSON-LD schema, sitemap, robots.txt

**অ্যাডমিন প্যানেল (`/admin`)**
- Dashboard — live counters, 14-day activity chart, latest messages/orders
- **Site Content** — homepage-এর প্রতিটা text এডিট করা যায় (hero, about, CTA…)
- Services / Process Steps / Why-Us cards / Counters / Testimonials / FAQs — full CRUD
- **Orders inbox** — status manage (New → Contacted → Completed/Cancelled)
- **Messages inbox** — read/reply/delete
- Subscribers list + CSV export
- Settings — logo name, contact, social links, SEO, footer; password change
- Testimonial ছবি upload (JPG/PNG/WEBP), icon picker

**সিকিউরিটি**
- Prepared statements (SQL injection proof), CSRF token সব ফর্মে
- bcrypt password hashing, session hardening, login rate-limit (৫ বার ভুল হলে ১০ মিনিট lock)
- Honeypot anti-spam + per-session throttle পাবলিক ফর্মে
- `.htaccess` দিয়ে config/database/internal files protected

---

## 🔁 Live Site-এ Change আনার ৩টি উপায়

**৯০% পরিবর্তনের জন্য Git লাগবেই না!** Admin panel থেকেই সব instantly live হয়:
text, headline, services, price, testimonials, FAQ, counters, contact info,
social links, ছবি upload, SEO — কিছুই code/deploy লাগে না।

কোনো **code/design পরিবর্তন** লাগলে:

| উপায় | কীভাবে | কখন ব্যবহার করবেন |
|---|---|---|
| ① **cPanel File Manager** | সরাসরি ফাইল এডিট → সাথে সাথে live | ছোট দ্রুত change |
| ② **cPanel Git pull** | Arena-তে change করলে বলুন → আমি GitHub-এ push → cPanel-এ "Update from Remote" ১ ক্লিক | বড়/structured update |
| ③ **Full Auto Deploy** | GitHub-এ push হলেই auto FTP দিয়ে live (`.github/workflows/deploy.yml` ready আছে — শুধু repo Settings → Secrets-এ `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` বসান) | ঘন ঘন update চাইলে |

> PR/merge **বাধ্যতামূলক না** — নিজের repo-তে সরাসরি push করলেই হবে। PR শুধু team review-র জন্য।

---

## 🔗 GitHub থেকে cPanel-এ Auto Deploy (Git Version Control)

cPanel → **Git™ Version Control** ফিচার থাকলে (hosting company enable করে দিতে পারে):

1. cPanel → **Git Version Control** → **Create**
2. **Clone URL:** `https://github.com/atikx2/growfy.git` দিন
   **Branch:** `arena/11e027a1-growfy`
   **Path:** `public_html` (খালি থাকতে হবে) অথবা উপ-ফোল্ডার
3. **Create** চাপুন — repo clone হয়ে যাবে
4. পরের বার update আনতে: **Pull or Deploy** → **Update from Remote** (GitHub-এ push করেই আপডেট)

> ⚠️ **সত্যি কথা:** এতে Vercel-এর মতো আলাদা automatic "preview link" পাবেন না — clone/deploy হওয়া ফোল্ডার যে domain/subdomain-এ point করা সেখানেই site দেখা যাবে। আলাদা preview/staging link চাইলে cPanel → **Subdomains** থেকে `preview.yourdomain.com` বানিয়ে ওই ফোল্ডারে repo clone করুন — সেটাই আপনার preview URL!

> 🔐 **গুরুত্বপূর্ণ:** Git pull করলে repo-র ফাইলগুলো তাজা হয় — কিন্তু আপনার DB password যাতে নষ্ট না হয়, credentials রাখুন **`config.local.php`**-তে (`config.local.php.sample` ফাইল copy করুন)। এটা git-এ থাকে না, pull করলেও অটো survive করে।

---

## 🚀 cPanel-এ ডিপ্লয় (ধাপে ধাপে)

### ধাপ ১ — ফাইল আপলোড
1. এই project-টা **zip** করুন (অথবা দেওয়া `growfy-cpanel.zip` ব্যবহার করুন)
2. cPanel → **File Manager** → `public_html` ফোল্ডারে যান
3. Zip আপলোড করে **Extract** করুন
   > সব ফাইল সরাসরি `public_html`-এর ভেতরে থাকতে হবে — অর্থাৎ `public_html/index.php`

### ধাপ ২ — ডাটাবেস (দুটো অপশন)

**অপশন A — MySQL (production-এর জন্য recommended)**
1. cPanel → **MySQL® Databases**-এ যান
2. নতুন database বানান (যেমন: `growfy_db`)
3. নতুন user বানান + strong password দিন
4. User-টাকে database-এ **Add** করুন (All Privileges দিন)
5. **`config.local.php.sample`** ফাইলটা copy করে নাম দিন **`config.local.php`**, তারপর এতে credentials বসান:

```php
define('DB_NAME', 'youruser_growfy_db');   // ← আপনার database নাম
define('DB_USER', 'youruser_growfy');       // ← আপনার user
define('DB_PASS', 'আপনার-password');        // ← password
```

> `config.local.php` git-এ track হয় না — GitHub deploy/pull করলেও নিরাপদ থাকে।

**অপশন B — কিছুই করবেন না (SQLite)**
- কোনো DB config না দিলে সাইট নিজে নিজে `data/growfy.db` ফাইলে চলবে
- ছোট/মিডিয়াম ট্রাফিকের জন্য একদম ঠিক আছে

### ধাপ ৩ — সাইট খুলুন
- `https://yourdomain.com` — সব টেবিল + ডেমো কনটেন্ট **স্বয়ংক্রিয়ভাবে তৈরি** হয়ে যাবে
- কোনো installer চালাতে হবে না

### ধাপ ৪ — Admin login
- যান: `https://yourdomain.com/admin`
- Default: **username:** `admin`  **password:** `Growfy@2026`
- প্রথম login-এই **নতুন password সেট করতে বলা হবে** (জোরালোভাবে করতে হবে) 🔒

### শেষ — কাস্টমাইজ
- Admin → **Settings**: নিজের email, WhatsApp number, social links বসান
- Admin → **Site Content**: headline ও text-গুলো বদলান
- Services-এ price ও description আপডেট করুন

> ⚠️ **PHP version**: cPanel → "Select PHP Version" থেকে **PHP 8.0 বা বেশি** রাখুন।

---

## 💻 লোকাল প্রিভিউ (কম্পিউটারে চালাতে চাইলে)

PHP install করার দরকার নেই — Node.js দিয়েই চলবে (PHP-WASM):

```bash
cd preview
npm install
node server.mjs
# → http://localhost:8080 (site) | http://localhost:8080/admin (admin)
```

অথবা PHP থাকলে সরাসরি: `php -S localhost:8080` (root ফোল্ডার থেকে)।

---

## 📁 ফাইল স্ট্রাকচার

```
├── index.php              # মূল পেজ (সব section + form handler)
├── config.php             # ← শুধু এই ফাইলে DB credentials দিন
├── includes/              # core: db, schema/seed, helpers (web থেকে protected)
│   └── sections/          # homepage-এর section template-গুলো
├── admin/                 # অ্যাডমিন প্যানেল (login protected)
├── assets/                # css, js, images (webp optimized)
├── uploads/               # admin-এ upload করা ছবি জমা হয়
├── data/                  # SQLite database (MySQL দিলে লাগে না) — protected
├── .htaccess              # security + caching + gzip
└── preview/               # Node প্রিভিউ সার্ভার (hosting-এ লাগে না)
```

## ❓ সমস্যা হলে

| সমস্যা | সমাধান |
|---|---|
| **500 error** | cPanel এ PHP 8.0+ সিলেক্ট করা আছে কিনা দেখুন |
| **DB connect হয় না** | `config.php`-র নাম/password আবার মিলান; cPanel এ user টা database-এ add করা আছে কিনা দেখুন |
| **Upload কাজ করে না** | `uploads/` ফোল্ডারকে File Manager থেকে ৭৫৫ permission দিন |
| **Forgot admin password** | Database এর `gf_admins` টেবিলের row মুছে দিন — site reload করলেই default `admin`/`Growfy@2026` আবার তৈরি হবে |
