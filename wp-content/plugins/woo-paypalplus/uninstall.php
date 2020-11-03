<?php

namespace WCPayPalPlus;

use WCPayPalPlus\Uninstall\Uninstaller;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$autoload = __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/inc/functions.php';

if (file_exists($autoload)) {
    /** @noinspection PhpIncludeInspection */
    require $autoload;
}
if (!class_exists(PayPalPlus::class)) {
    return;
}

$container = resolve();
$bootstrapper = new Bootstrapper($container, __DIR__ . '/paypalplus-woocommerce.php');
$bootstrapper->bootstrap();

$uninstaller = $container->get(Uninstaller::class);

is_multisite()
    ? $uninstaller->multisiteUninstall()
    : $uninstaller->uninstall();
