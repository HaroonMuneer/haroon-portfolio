# Haroon Muneer Portfolio: Elementor theme

Black and gold portfolio theme. Every section is a native Elementor container or widget, so you can edit, remove or add anything in the Elementor editor.

## Requirements
- WordPress 6.0+, PHP 7.4+
- Elementor (free) 3.18 or newer, with **Containers** enabled (Elementor > Settings > Features)

## Install
1. Appearance > Themes > Add New > Upload Theme, choose `haroon-portfolio-theme.zip`, activate.
2. Install and activate the free Elementor plugin.
3. Appearance > **Import Demo** > Import now. This creates Home, About, Services, Projects, Skills, Blog and Contact, uploads the images, adds 4 sample posts, builds the menu and sets the home page.
4. Appearance > Customize > **Portfolio Details**: set your email (the contact form sends here), social links and CV file URL.
5. Open any page and choose **Edit with Elementor**.

## What is editable in Elementor
Headings, text, buttons, images, icon boxes, counters, progress bars, icon lists and toggles are all standard widgets. Duplicate a project card container to add a project, or delete any container to remove a section.

Style comes from CSS classes (for example `hm-project-card`, `hm-hero-title`) written in `assets/css/theme.css`. Keep those classes on a widget or container if you want it to keep its look. Remove a class to turn the styling off.

- **Project filter buttons:** give a card the class `hm-cat-<name>` and a filter button `hm-filter hm-filter-<name>`.
- **Blog grid and contact form:** these are shortcodes in an Elementor Shortcode widget: `[hm_posts count="9"]` and `[hm_contact_form]`.
- **Download CV buttons:** set the CV URL in the Customizer before importing, or paste the link into the button in Elementor.

## Notes
- Project and blog images are generated placeholders. Replace them in Elementor with real screenshots.
- Fonts are bundled in `assets/fonts`, so no Google Fonts request is made.
- Not yet tested on a live WordPress and Elementor install. Run the import on a staging site first.
