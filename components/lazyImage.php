<?php
/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) 2016 Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;

$app = App::get();
$context = $app->context->get(__FILE__);

$aspectRatio = null;
$temp = (string) $component->aspectRatio;
if (preg_match('/^[0-9\.]+:[0-9\.]+$/', $temp) === 1) {
    $temp = explode(':', $temp);
    $aspectRatio = [(double) $temp[0], (double) $temp[1]];
}
unset($temp);

$loadingBackground = 'checkered';
$temp = (string) $component->loadingBackground;
if ($temp !== '') {
    if (array_search($temp, ['checkered', 'none']) !== false) {
        $loadingBackground = $temp;
    }
}

$localCache = [];
$getImageSize = function($filename) use ($app, &$localCache) {
    $cacheKey = 'lazy-image-size-' . $filename;
    if (isset($localCache[$cacheKey])) {
        return $localCache[$cacheKey];
    }
    $cachedData = $app->cache->getValue($cacheKey);
    if ($cachedData !== null) {
        $size = json_decode($cachedData, true);
        $localCache[$cacheKey] = $size;
        return $size;
    }
    try {
        $size = $app->images->getSize($filename);
    } catch (\Exception $ex) {
        $size = [1, 1];
    }
    $localCache[$cacheKey] = $size;
    $app->cache->set($app->cache->make($cacheKey, json_encode($size)));
    return $size;
};

$containerStyle = 'display:inline-block;width:100%;overflow:hidden;';

$filename = (string) $component->filename;
if ($filename !== '') {
    try {
        list($imageWidth, $imageHeight) = $getImageSize($filename);
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
            $options = ['width' => (int) $width];
            if ($options['width'] < 1) {
                $options['width'] = 1;
            }
            if ($aspectRatio !== null) {
                $options['height'] = (int) ($width * $aspectRatio[1] / $aspectRatio[0]);
                if ($options['height'] > $imageHeight) {
                    return;
                }
                if ($options['height'] < 1) {
                    $options['height'] = 1;
                }
            }
            $options['cacheMaxAge'] = 999999999;
            $options['version'] = 1;
            $versions[] = $app->assets->getUrl($filename, $options) . ' ' . $width . 'w';
//            $options['outputType'] = 'webp';
//            $versions[] = $app->assets->getUrl($filename, $options) . ' ' . $width . 'w';
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

$attributes = '';
$style = 'display:block;';
if ($aspectRatio !== null) {
    $style .= 'padding-bottom:' . (number_format($aspectRatio[1] / $aspectRatio[0], 6, '.', '') * 100) . '%;';
}
if ($loadingBackground === 'checkered') {
    $attributes .= ' data-onlazyload="this.style.backgroundImage=\'none\';"';
    $style .= 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUAQMAAAC3R49OAAAABlBMVEUAAAD///+l2Z/dAAAAAnRSTlMZGYn4zOAAAAAUSURBVAjXY2Sw38hIDP5/0IEYDADG0R1147/PtQAAAABJRU5ErkJggg==\');';
}
$srcAttribute = isset($originalUrl) ? ' src="' . htmlentities($originalUrl) . '"' : '';
$dataSrcsetAttribute = isset($versions) ? ' data-srcset="' . htmlentities(implode(', ', $versions)) . '"' : '';

$class = (string) $component->class;
$classAttribute = isset($class{0}) ? ' class="' . htmlentities($class) . '"' : '';
$alt = (string) $component->alt;
$altAttribute = isset($alt{0}) ? ' alt="' . htmlentities($alt) . '"' : ' alt=""';
$title = (string) $component->title;
$titleAttribute = isset($title{0}) ? ' title="' . htmlentities($title) . '"' : '';
?><html>
    <head><?php
?><style id="lazy-image-bearframework-addon-style">.responsively-lazy:not(img){position:relative;height:0;}.responsively-lazy:not(img)>img{position:absolute;top:0;left:0;width:100%;height:100%}img.responsively-lazy{width:100%;}</style><?php
?></head>
    <body><?php
        echo '<span' . $classAttribute . ' style="' . $containerStyle . htmlentities($component->style) . '">';
        echo '<span class="responsively-lazy"' . $attributes . ' style="' . $style . '">';
        echo '<img ' . $altAttribute . $titleAttribute . $srcAttribute . $dataSrcsetAttribute . ' srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />';
        echo '</span>';
        echo '</span>';
        echo '<script id="lazy-image-bearframework-addon-script" src="' . $context->assets->getUrl('assets/responsivelyLazy.min.js', ['cacheMaxAge' => 999999999, 'version' => 2]) . '" async/>';
        ?></body>
</html>