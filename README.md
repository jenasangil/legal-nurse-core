# Legal Nurse Core

**Legal Nurse Core** is a custom WordPress plugin developed by Grow Enrollments for the Legal Nurse website. It provides site-specific custom functionality, Elementor widgets, integrations, shortcodes, and utility functions that are critical to the operation and presentation of the site.

## Features

### Custom Elementor Widgets
This plugin registers several native Elementor widgets specifically tailored for the Legal Nurse brand:
- **Featured Carousel**: A dynamic, swiper-based carousel that pulls specific pages/posts into filterable categories. Includes extensive custom styling controls directly inside the Elementor editor.
- **Child Pages**: Automatically displays child pages in a grid layout. Supports featured images and excerpts.
- **ACF Content**: Integrates with Advanced Custom Fields (ACF) to output custom data.
- **Compare Table**: A comparison table layout widget.
- **Loop Filter**: Handles custom post loops and filtering logic.
- **Social Share**: Custom-styled social sharing buttons.

### Assets
The plugin enqueues its own optimized styles and scripts for its custom widgets:
- Employs **Swiper.js** for carousels.
- Dynamically loads custom typography (Wix Madefor Display & Text).
- Utilizes cache-busting `filemtime` for seamless CSS/JS updates.

## Installation

1. Clone or download this repository.
2. Upload the `legal-nurse-core-main` directory to the `/wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Development

- **Scripts & Styles**: Main frontend styles are located in `assets/css/` and JS in `assets/js/`.
- **Elementor Widgets**: All Elementor widget classes are located in the `includes/` directory and loaded automatically via `legal-nurse-core.php`.

## Author
**Grow Enrollments**  
https://growenrollments.com

## License
GPL-2.0-or-later
