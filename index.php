<?php

/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;

$app = App::get();
$context = $app->contexts->get(__FILE__);

$context->assets
        ->addDir('assets');

$app->components
        ->addAlias('lazy-image', 'file:' . $context->dir . '/components/lazyImage.php');

$app->clientShortcuts
        ->add('-ivopetkov-lazy-image-responsively-lazy', function(IvoPetkov\BearFrameworkAddons\ClientShortcut $shortcut) use ($context) {
            $shortcut->requirements[] = [
                'type' => 'file',
                'url' => $context->assets->getURL('assets/responsivelyLazy.min.js', ['cacheMaxAge' => 999999999, 'version' => 2]),
                'async' => true,
                'mimeType' => 'text/javascript'
            ];
            $shortcut->requirements[] = [
                'type' => 'text',
                'value' => '.responsively-lazy:not(img){position:relative;height:0;}.responsively-lazy:not(img)>img{position:absolute;top:0;left:0;width:100%;height:100%}img.responsively-lazy{width:100%;}',
                'mimeType' => 'text/css'
            ];
        });
