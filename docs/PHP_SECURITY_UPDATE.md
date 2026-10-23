# PHP and CodeIgniter security update — 23 October 2026

The main repository and Railway source are `arclast988/Terminal-Queue-Monitoring`.

Railway now builds from `Dockerfile`, using the official `php:8.2.35-fpm-bookworm`
image. PHP 8.2.35 contains the fixes for CVE-2026-109618 and CVE-2026-109619.
The old Nixpacks snapshot is no longer the production build source. CLI and
PHP-FPM versions are printed at startup so the deployed runtime can be verified
in Railway logs. Update the image tag and its verification checks for subsequent
PHP security releases.

The bundled CodeIgniter framework is updated to 4.7.5. Existing application
hardening is preserved, and restricted Parser conditionals are enabled. The
framework regression tests cover escaped substitutions, template source
protection, and restricted conditional expressions.

For the local Ubuntu 26.04 installation, `8.5.4-0ubuntu1.5` is patched even though
`php -v` displays 8.5.4. Ubuntu backports security fixes into its package revision.
Both `php8.5-cli` and `php8.5-fpm` must have that revision or later, and PHP-FPM
must be restarted after an update. The locally served project directory must
also contain the framework update; updating `C:\jeepneynvans` does not update a
separate checkout under `/home/jaylo/jeepneynvans`.

References:

- [PHP 8.2.35 security release](https://www.php.net/releases/8_2_35.php)
- [Ubuntu security notice USN-8948-1](https://ubuntu.com/security/notices/USN-8948-1)
- [CodeIgniter 4.7.5 release](https://github.com/codeigniter4/CodeIgniter4/releases/tag/v4.7.5)
