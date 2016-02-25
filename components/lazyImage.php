<?php
/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) 2016 Ivo Petkov
 * Free to use under the MIT license.
 */

$aspectRatio = null;
if (strlen($component->aspectRatio) > 0) {
    if (preg_match('/^[0-9\.]+:[0-9\.]+$/', $component->aspectRatio) === 1) {
        $aspectRatio = explode(':', $component->aspectRatio);
        $aspectRatio[0] = (double) $aspectRatio[0];
        $aspectRatio[1] = (double) $aspectRatio[1];
    }
}
$fileName = $component->filename;
list($imageWidth, $imageHeight) = $app->images->getSize($fileName);
$versions = [];
$addVersionUrl = function($width) use ($app, &$versions, $fileName, $aspectRatio, $imageHeight) {
    $options = ['width' => $width];
    if ($aspectRatio !== null) {
        $options['height'] = $width * $aspectRatio[1] / $aspectRatio[0];
        if ($options['height'] > $imageHeight) {
            return;
        }
    }
    $versions[] = $app->assets->getUrl($fileName, $options) . ' ' . $width . 'w';
};
for ($width = 200; $width <= $imageWidth; $width+=200) {
    $addVersionUrl($width);
}
if ($aspectRatio !== null) {
    $addVersionUrl(floor($imageHeight / $aspectRatio[1] * $aspectRatio[0]));
}
$addVersionUrl($imageWidth);
$versions = array_unique($versions);
$originalVersion = array_pop($versions);
$originalUrl = explode(' ', $originalVersion)[0];

if ($aspectRatio === null) {
    $aspectRatio = [$imageWidth, $imageHeight];
}
?><html>
    <head>
        <style>
            .responsively-lazy:not(img){position:relative;height:0}.responsively-lazy:not(img) img{position:absolute;top:0;left:0;width:100%;height:100%}img.responsively-lazy{width:100%;}
        </style>
    </head>
    <body><?php
        echo '<div style="' . htmlentities($component->style) . '">';
        echo '<div class="responsively-lazy" style="padding-bottom:' . (number_format($aspectRatio[1] / $aspectRatio[0], 6, '.', '') * 100) . '%;">';
        echo '<img alt="' . htmlentities($component->alt) . '" title="' . htmlentities($component->title) . '" src="' . htmlentities($originalUrl) . '" data-srcset="' . htmlentities(implode(', ', $versions)) . '" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />';
        echo '</div>';
        echo '<script src="' . $context->assets->getUrl('assets/responsivelyLazy.min.js') . '"/>';
        echo '</div>';
        ?></body>
</html>