# Haroon Muneer Portfolio: Elementor theme

Black and gold portfolio theme. Every section is a native Elementor container or widget, so you can edit, remove or add anything in the Elementor editor.

## Requirements
- WordPress 6.0+, PHP 7.4+
- Elementor (free) 3.18 or newer, with **Containers** enabled (Elementor > Settings > Features)

## Install
1. Appearance > Themes > Add New > Upload Theme, choose `haroon-portfolio-theme.zip`, activate.
2. Install and activate the free Elementor plugin.
3. Appearance > **Import Demo**. The page shows a checklist (Elementor, Containers, PHP, uploads folder). Click **Import now**. This creates Home, About, Services, Projects, Skills, Blog and Contact, uploads the images, adds 4 sample posts and 6 sample projects, builds the menu and sets the home page, then shows a report of what was done and any problems. It is safe to run again; nothing is duplicated.
4. Appearance > Customize > **Portfolio Details** (email, social links, CV link, footer text, back-to-top button) and **Portfolio Colours** (gold, light gold, background).
5. Open any page and choose **Edit with Elementor**.

## What is editable in Elementor
Headings, text, buttons, images, icon boxes, counters, progress bars, icon lists and toggles are all standard widgets. Duplicate a project card container to add a project, or delete any container to remove a section.

Style comes from CSS classes (for example `hm-project-card`, `hm-hero-title`) written in `assets/css/theme.css`. Keep those classes on a widget or container if you want it to keep its look. Remove a class to turn the styling off.

- **Project filter buttons:** give a card the class `hm-cat-<name>` and a filter button `hm-filter hm-filter-<name>`.
- **Blog grid and contact form:** these are shortcodes in an Elementor Shortcode widget: `[hm_posts count="9"]` and `[hm_contact_form]`.
- **Download CV buttons:** set the CV URL in the Customizer before importing, or paste the link into the button in Elementor.

## Projects post type
Under **Projects** in the admin you can add projects with a featured image, categories, type, tech stack, tags and live URL. They get their own pages at `/work/` and `/work-category/<name>/`. To show them on any page, add an Elementor Shortcode widget with `[hm_projects count="6"]` (use `filter="no"` to hide the filter buttons, or `category="ecommerce"` to show one category). The static project cards on the Home and Projects pages stay fully editable in Elementor too.

## WooCommerce
If WooCommerce is active the shop, product, cart, checkout and account pages use the black and gold styling, and a cart link appears in the header.

## Notes
- Project and blog images are generated placeholders. Replace them in Elementor with real screenshots.
- Fonts are bundled in `assets/fonts`, so no Google Fonts request is made.
- Not yet tested on a live WordPress and Elementor install. Run the import on a staging site first.
