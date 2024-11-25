# Yoast-sitemap-fix
Splits Yoast sitemaps by Polylang languages, redirects incorrect language URLs, and excludes post type archives from sitemaps.

# How to Test

Check Sitemaps:
Visit /sitemap_index.xml, /lv/sitemap_index.xml, and /et/sitemap_index.xml, etc....
Ensure that each sitemap contains only the sections and links specific to the language.

Verify Sitemap Content:
Visit /et/page-sitemap.xml or /lv/page-sitemap.xml.
Confirm that only pages with the corresponding language are listed.

Test Invalid URLs:
Visit an invalid URL like /etее/sitemap_index.xml.
Confirm that it redirects to /sitemap_index.xml.

Check Post Type Archives:
Ensure that archive links for custom post types are excluded from all sitemaps.
