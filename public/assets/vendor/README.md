These dependencies are served with the application so layout, buttons, icons,
and typography do not depend on third-party CDN requests at runtime.

- Bootstrap 5.3.0: CSS and bundled JavaScript, MIT license.
- Font Awesome Free 6.4.0: CSS and Solid, Regular, and Brands fonts. See LICENSE.txt.
- Bootstrap Icons 1.11.0: CSS and fonts, MIT license.
- Bricolage Grotesque, Inter, and Outfit: variable WOFF2 fonts from Google Fonts,
  Latin and Latin Extended subsets, with font-display: swap. Their OFL licenses
  are included in the fonts directory. Filenames contain their content hashes.

Stylesheet and Bootstrap script URLs use app_asset_url(), which derives a cache
version from the file contents. Preserve the upstream licenses when updating.
