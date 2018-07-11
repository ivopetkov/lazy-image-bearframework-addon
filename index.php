<?php

/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;

$app = App::get();
$context = $app->context->get(__FILE__);

$context->assets->addDir('assets');

$app->components->addAlias('lazy-image', 'file:' . $context->dir . '/components/lazyImage.php');
