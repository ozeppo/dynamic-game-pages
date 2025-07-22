# 🎮 Dynamic Game Pages

**Dynamic Game Pages** is a WordPress plugin that automatically generates game subpages based on live Steam API data — perfect for SEO, game directories, affiliate sites, and more.

---

## ✨ Features

* Fetches real-time game data from Steam (title, description, price, requirements, etc.)
* Creates SEO-friendly URLs like `/game/{appid}`
* Customizable shortcode banner: `[game_banner appid="123456"]`
* Editable color theme and FAQ text
* Supports multiple languages (based on Steam data availability)
* Outputs Schema.org FAQ & VideoGame JSON-LD for rich search results

---

## 📦 Installation

1. Download or clone the plugin:

   ```bash
   git clone https://github.com/ozeppo/dynamic-game-pages.git
   ```
2. Copy the folder into your WordPress `/wp-content/plugins/` directory
3. Go to **Plugins > Installed Plugins** in WordPress and activate "Dynamic Game Pages"
4. Navigate to **Settings > Dynamic Game Pages** to configure

---

## ⚙️ Configuration

Available settings:

* **Game Slug** – base slug for game pages (e.g. `/game/1091500`)
* **Accent Color** – used in buttons and section headers
* **Steam Language** – language used in API requests (`english`, `polish`, `german`, etc.)
* **FAQ Templates** – dynamic text placeholders:

  * `%title` – game name
  * `%release` – release date
  * `%requirements` – system requirements

> ⚠️ After changing the slug, go to **Settings > Permalinks** and click “Save Changes” to refresh rewrite rules.

---

## 🧹 Shortcode

Use this shortcode anywhere in your site:

```php
[game_banner appid="1091500" button="Buy on Steam"]
```

Options:

* `appid` (required) – Steam App ID
* `button` (optional) – custom button label

---

## 🔍 Example Output

Generated game page includes:

* Game title & header image
* Description, release date, genre
* Publisher & developer info
* Price (converted via Steam API)
* System requirements (minimum & recommended)
* FAQ section with structured JSON-LD

---

## 🌐 Translations

* Fully gettext-compatible (`__()`, `_e()`, etc.)
* Translation-ready via `.pot` file in `/languages/`
* Works great with Loco Translate or WPML

---

## 💪 Requirements

* WordPress 5.8 or higher
* PHP 7.4 or higher
* External HTTP access (for Steam API)

---

## 👨‍💻 Author

**Filip Chmielecki**
[filipchmielecki.pl](https://filipchmielecki.pl)
[wiemwcogram.pl](https://wiemwcogram.pl)
Plugin created to support content creators and SEO-focused game websites.

---

## 🧠 Contributions & Support

Suggestions and bug reports welcome!
Please submit issues or pull requests on [GitHub](https://github.com/your-username/dynamic-game-pages/issues)
