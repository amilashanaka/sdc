# Complete Project Structure with Assets

```
project/
├── index.php
├── .htaccess
├── db/
|    ├── database.sql
├── README.md
│
├── app/
│   ├── core/
│   │   ├── Database.php
│   │   └── App.php
│   ├── controllers/
│   │   ├── LoginController.php
│   │   └── DashboardController.php
│   ├── models/
│   │   └── User.php
│   └── views/
│       ├── login/
│       │   └── index.php
│       └── dashboard/
│           └── index.php
│
├── config/
│   └── database.php
│
├── assets/                          ← PUBLIC ASSETS FOLDER
│   ├── css/
│   │   ├── style.css               ← Main stylesheet
│   │   ├── login.css               ← Login-specific styles
│   │   ├── dashboard.css           ← Dashboard-specific styles
│   │   └── theme.css               ← Theme variables
│   │
│   ├── js/
│   │   ├── app.js                  ← Main JavaScript
│   │   ├── login.js                ← Login-specific JS
│   │   ├── dashboard.js            ← Dashboard-specific JS
│   │   └── theme.js                ← Theme toggle logic
│   │
│   ├── img/                         ← Images
│   │   ├── logo.png
│   │   ├── avatar-default.png
│   │   └── icons/
│   │
│   ├── fonts/                       ← Custom fonts (optional)
│   │   └── custom-font.woff2
│   │
│   └── vendor/                      ← Third-party libraries (optional)
│       ├── bootstrap/
│       ├── fontawesome/
│       └── sweetalert2/
│
└── storage/                         ← File uploads, logs (optional)
    ├── uploads/
    └── logs/
```

# 📚 Complete Assets Management Guide

## 🎯 Quick Answer

**Put your CSS and JS files in the `public/` folder:**

```
project/
└── public/
    ├── css/
    │   └── style.css
    ├── js/
    │   └── app.js
    └── img/
        └── logo.png
```

## 📋 Three Ways to Load Assets

### ✅ Method 1: Using Helper Class (RECOMMENDED)

**Setup:**

1. Create `config/app.php` with base URL
2. Create `app/core/Helper.php` with asset methods
3. Use in views:

```php
<?php require_once __DIR__ . '/../../core/Helper.php'; ?>

<!-- Load CSS -->
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">

<!-- Load JS -->
<script src="<?= Helper::asset('js/app.js') ?>"></script>

<!-- Load Image -->
<img src="<?= Helper::asset('img/logo.png') ?>" alt="Logo">

<!-- With cache busting -->
<link rel="stylesheet" href="<?= Helper::assetVersion('css/style.css') ?>">
```

**Benefits:**

- ✅ Easy to change URLs in one place
- ✅ Works in any environment
- ✅ Cache busting support
- ✅ Clean, maintainable code

---

### Method 2: Direct Paths (SIMPLE)

```php
<!-- Absolute path from root -->
<link rel="stylesheet" href="/public/css/style.css">
<script src="/public/js/app.js"></script>
<img src="/public/img/logo.png">
```

**Benefits:**

- ✅ Simple and straightforward
- ✅ No setup needed

**Drawbacks:**

- ❌ Harder to change if you move the project
- ❌ No cache busting

---

### Method 3: PHP Constants

**In `index.php`:**

```php
define('BASE_URL', 'http://localhost/project');
define('ASSETS', BASE_URL . '/public');
```

**In views:**

```php
<link rel="stylesheet" href="<?= ASSETS ?>/css/style.css">
<script src="<?= ASSETS ?>/js/app.js"></script>
```

---

## 📁 Recommended Folder Structure

```
public/
├── css/
│   ├── style.css          ← Main styles
│   ├── login.css          ← Page-specific
│   ├── dashboard.css
│   └── components/
│       ├── buttons.css
│       ├── forms.css
│       └── cards.css
│
├── js/
│   ├── app.js             ← Main JavaScript
│   ├── login.js           ← Page-specific
│   ├── dashboard.js
│   └── utils.js           ← Utilities
│
├── img/
│   ├── logo.png
│   ├── logo-dark.png
│   ├── favicon.ico
│   └── icons/
│       ├── user.svg
│       └── dashboard.svg
│
├── fonts/                  ← Custom fonts (optional)
│   └── custom-font.woff2
│
└── vendor/                 ← Third-party libs (optional)
    ├── bootstrap/
    └── fontawesome/
```

---

## 🎨 Example: Complete Login Page

```php
<?php require_once __DIR__ . '/../../core/Helper.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- Your CSS -->
    <link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= Helper::asset('css/login.css') ?>">

    <!-- OR use direct paths -->
    <link rel="stylesheet" href="/public/css/style.css">

    <!-- CDN Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="login-container">
        <!-- Logo -->
        <img src="<?= Helper::asset('img/logo.png') ?>" alt="Logo">

        <!-- Form -->
        <form action="<?= Helper::url('login/authenticate') ?>" method="POST">
            <!-- form fields -->
        </form>
    </div>

    <!-- Scripts at bottom -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Helper::asset('js/app.js') ?>"></script>
    <script src="<?= Helper::asset('js/login.js') ?>"></script>
</body>
</html>
```

---

## 🔧 .htaccess Configuration

Make sure your `.htaccess` allows access to the `public/` folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Allow direct access to public folder
    RewriteCond %{REQUEST_URI} ^/public/
    RewriteRule ^ - [L]

    # Route everything else to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

---

## 🚀 Performance Tips

### 1. Load Order Matters

```html
<head>
    <!-- Critical CSS first -->
    <style>
        /* Inline critical styles for above-the-fold */
    </style>

    <!-- External CSS -->
    <link rel="stylesheet" href="...">
</head>
<body>
    <!-- Content -->

    <!-- Scripts at bottom before </body> -->
    <script src="..."></script>
</body>
```

### 2. Minify for Production

```bash
# Minify CSS
npx minify public/css/style.css > public/css/style.min.css

# Minify JS
npx minify public/js/app.js > public/js/app.min.js
```

**In production views:**

```php
<?php
$env = 'production'; // Change based on environment
$suffix = ($env === 'production') ? '.min' : '';
?>
<link rel="stylesheet" href="<?= Helper::asset("css/style{$suffix}.css") ?>">
```

### 3. Cache Busting

```php
<!-- Automatic version based on file modification time -->
<link rel="stylesheet" href="<?= Helper::assetVersion('css/style.css') ?>">

<!-- Outputs: public/css/style.css?v=1234567890 -->
```

### 4. Combine Files

**production.css** (combine multiple CSS files):

```css
@import url('variables.css');
@import url('reset.css');
@import url('components.css');
@import url('layout.css');
```

---

## 📦 Using CDN with Fallback

```html
<!-- Load from CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

<!-- Fallback to local if CDN fails -->
<script>
if (typeof bootstrap === 'undefined') {
    document.write('<script src="<?= Helper::asset('vendor/bootstrap/js/bootstrap.min.js') ?>"><\/script>');
}
</script>
```

---

## 🎯 Best Practice Summary

| Aspect            | Recommendation                               |
| ----------------- | -------------------------------------------- |
| **Location**      | `public/` folder                             |
| **Access Method** | `Helper::asset()` or direct `/public/` paths |
| **Organization**  | By type (css/, js/, img/)                    |
| **CDN**           | Use for libraries (Bootstrap, FontAwesome)   |
| **Custom Code**   | Store locally in `public/`                   |
| **Cache**         | Use versioning (`?v=timestamp`)              |
| **Production**    | Minify and combine files                     |
| **Load Order**    | CSS in `<head>`, JS before `</body>`         |

---

## ✅ Complete Working Example

**File Structure:**

```
project/
├── index.php
├── config/
│   ├── app.php
│   └── database.php
├── app/
│   └── core/
│       └── Helper.php
└── public/
    ├── css/
    │   └── style.css
    └── js/
        └── app.js
```

**Usage in any view:**

```php
<?php require_once __DIR__ . '/../../core/Helper.php'; ?>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<script src="<?= Helper::asset('js/app.js') ?>"></script>
```

**That's it!** Simple, clean, and professional. 🚀

---

## 🆘 Troubleshooting

### Assets not loading?

1. **Check path:**
   
   ```php
   <?php
   echo Helper::asset('css/style.css');
   // Should output: http://localhost/project/public/css/style.css
   ?>
   ```

2. **Check file exists:**
   
   ```bash
   ls -la public/css/style.css
   ```

3. **Check .htaccess allows access**

4. **Check browser console** for 404 errors

5. **Verify base_url in config/app.php**

---

## 🎓 Advanced: Organizing Large Projects

For large projects, use this structure:

```
public/
├── css/
│   ├── vendor/           ← Third-party CSS
│   ├── common/           ← Shared styles
│   ├── components/       ← Reusable components
│   └── pages/            ← Page-specific styles
│
└── js/
    ├── vendor/
    ├── common/
    ├── components/
    └── pages/
```

This keeps your code **organized and maintainable**! 🎨