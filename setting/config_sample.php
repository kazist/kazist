<?php

$SYSTEM_CONFIG;
$sc->setParameter('seo.system_title_on_pages', '1');
$sc->setParameter('seo.system_title_at', 'end');
$sc->setParameter('seo.enable', '1');
$sc->setParameter('seo.extension', 'html');
$sc->setParameter('seo.rewrite', '1');
$sc->setParameter('seo.minified', '0');
$sc->setParameter('seo.merge', '0');
$sc->setParameter('seo.browser_cache', '1');

/* -------------------------- Cache settings ------------------- */

$sc->setParameter('cache.enable', '1');
$sc->setParameter('cache.folder', 'files/cache/twig');

/* -------------------------- Ftp settings ------------------- */

$sc->setParameter('ftp.host', 'localhost');
$sc->setParameter('ftp.port', '21');
$sc->setParameter('ftp.username', 'ftproot');
$sc->setParameter('ftp.password', 'ftproot');
$sc->setParameter('ftp.directory', 'kazist/');

/* -------------------------- Themes settings ------------------- */

$sc->setParameter('theme.default', 'awesome');

/* -------------------------- System settings ------------------- */

$sc->setParameter('system.logging', false);
$sc->setParameter('system.debugging', false);
$sc->setParameter('system.timeout', '15');
$sc->setParameter('system.stage', 'production');
$sc->setParameter('system.list_limit', '10');
$sc->setParameter('system.gzip', '0');
$sc->setParameter('system.title', 'Kaziframework');
$sc->setParameter('system.offset', 'awesome');
$sc->setParameter('system.seo_extension', 'html');
$sc->setParameter('system.seo_rewrite', '1');
$sc->setParameter('system.error_reporting', 'E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR');
$sc->setParameter('system.error_display', 'on');


