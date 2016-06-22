<?php
/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) 2016 Ivo Petkov
 * Free to use under the MIT license.
 */

$aspectRatio = null;
$temp = (string) $component->aspectRatio;
if ($temp !== '') {
    if (preg_match('/^[0-9\.]+:[0-9\.]+$/', $temp) === 1) {
        $temp = explode(':', $temp);
        $aspectRatio = [(double) $temp[0], (double) $temp[1]];
    }
}

$containerStyle = 'display:block;margin:0 auto;';

$filename = (string) $component->filename;
if ($filename !== '') {
    try {
        list($imageWidth, $imageHeight) = $app->images->getSize($filename);
    } catch (\Exception $e) {
        if ($app->config->displayErrors) {
            throw $e;
        }
        $imageWidth = 0;
        $imageHeight = 0;
    }
    if ($imageWidth > 0 && $imageHeight > 0) {
        $containerStyle .= 'max-width:' . $imageWidth . 'px;max-height:' . $imageHeight . 'px;';
        $versions = [];
        $addVersionUrl = function($width) use ($app, &$versions, $filename, $aspectRatio, $imageHeight) {
            $options = ['width' => $width];
            if ($aspectRatio !== null) {
                $options['height'] = $width * $aspectRatio[1] / $aspectRatio[0];
                if ($options['height'] > $imageHeight) {
                    return;
                }
            }
            $versions[] = $app->assets->getUrl($filename, $options) . ' ' . $width . 'w';
        };
        for ($width = 200; $width <= $imageWidth; $width += 200) {
            $addVersionUrl($width);
        }
        if ($aspectRatio !== null) { // version for the max height
            $addVersionUrl(floor($imageHeight / $aspectRatio[1] * $aspectRatio[0]));
        }
        $addVersionUrl($imageWidth); // version for the max width
        $versions = array_unique($versions);
        $originalVersion = array_pop($versions);
        $originalUrl = explode(' ', $originalVersion)[0];

        if ($aspectRatio === null) {
            $aspectRatio = [$imageWidth, $imageHeight];
        }
    }
}

$style = 'display:block;background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUAQMAAAC3R49OAAAABlBMVEUAAAD///+l2Z/dAAAAAnRSTlMZGYn4zOAAAAAUSURBVAjXY2Sw38hIDP5/0IEYDADG0R1147/PtQAAAABJRU5ErkJggg==\');';
if ($aspectRatio !== null) {
    $style .= 'padding-bottom:' . (number_format($aspectRatio[1] / $aspectRatio[0], 6, '.', '') * 100) . '%;';
}
$class = (string) $component->class;
$classAttribute = isset($class{0}) ? ' class="' . htmlentities($class) . '"' : '';
$alt = (string) $component->alt;
$altAttribute = isset($alt{0}) ? ' alt="' . htmlentities($alt) . '"' : ' alt=""';
$title = (string) $component->title;
$titleAttribute = isset($title{0}) ? ' title="' . htmlentities($title) . '"' : '';
$srcAttribute = isset($originalUrl) ? ' src="' . htmlentities($originalUrl) . '"' : '';
$dataSrcsetAttribute = isset($versions) ? ' data-srcset="' . htmlentities(implode(', ', $versions)) . '"' : '';
?><html>
    <head>
        <style id="lazy-image-bearframework-addon-style">
            .responsively-lazy:not(img){position:relative;height:0;}.responsively-lazy:not(img) img{position:absolute;top:0;left:0;width:100%;height:100%}img.responsively-lazy{width:100%;}
        </style>
        <script id="lazy-image-bearframework-addon-script" src="<?= $context->assets->getUrl('assets/responsivelyLazy.min.js'); ?>" async/>
    </head>
    <body><?php
        echo '<span' . $classAttribute . ' style="' . $containerStyle . htmlentities($component->style) . '">';
        echo '<span class="responsively-lazy" data-onlazyload="this.style.backgroundImage=\'none\';" style="' . $style . '">';
        echo '<img ' . $altAttribute . $titleAttribute . $srcAttribute . $dataSrcsetAttribute . ' srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />';
        echo '</span>';
        echo '</span>';
        ?></body>
</html>