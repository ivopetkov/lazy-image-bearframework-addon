<?php

/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

BearFramework\Addons::register('ivopetkov/lazy-image-bearframework-addon', __DIR__, [
    'require' => [
        'ivopetkov/html-server-components-bearframework-addon',
        'ivopetkov/client-shortcuts-bearframework-addon'
    ]
]);
