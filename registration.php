<?php

// PSR-4 Fallback
spl_autoload_register(static function ($class) {
    $prefix = 'TrustComponent\\TrustCaptchaMagento2\\';
    $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0) {
        return;
    }
    $rel  = substr($class, $len);
    $file = __DIR__ . '/' . str_replace('\\', '/', $rel) . '.php';
    if (is_file($file)) {
        require $file;
    }
}, true, true);

use Magento\Framework\Component\ComponentRegistrar;
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'TrustComponent_TrustCaptchaMagento2',
    __DIR__
);
