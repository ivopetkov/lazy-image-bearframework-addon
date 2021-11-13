<?php
/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;

$app = App::get();
$context = $app->contexts->get(__DIR__);

$aspectRatio = null;
$temp = (string) $component->aspectRatio;
if (preg_match('/^[0-9\.]+:[0-9\.]+$/', $temp) === 1) {
    $temp = explode(':', $temp);
    $aspectRatio = [(float) $temp[0], (float) $temp[1]];
}
unset($temp);

$loadingBackground = 'checkered';
$temp = (string) $component->loadingBackground;
if ($temp !== '') {
    if (array_search($temp, ['checkered', 'none']) !== false) {
        $loadingBackground = $temp;
    }
}

$maxSize = (int)$component->maxSize;
if ($maxSize === 0) {
    $maxSize = null;
}

$quality = $component->getAttribute('quality');
$quality = strlen($quality) === 0 ? null : (int)$quality;

$getImageSize = function ($filename) use ($app) {
    $details = $app->assets->getDetails($filename, ['width', 'height']);
    return [$details['width'], $details['height']];
};

$containerStyle = 'display:inline-block;width:100%;overflow:hidden;';

$originalURL = null;

$filename = (string) $component->filename;
if ($filename !== '') {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    list($imageWidth, $imageHeight) = $getImageSize($filename);
    if ($maxSize !== null) {
        if ($imageWidth > $maxSize) {
            $imageHeight = floor($maxSize / $imageWidth * $imageHeight);
            $imageWidth = $maxSize;
        }
        if ($imageHeight > $maxSize) {
            $imageWidth = floor($maxSize / $imageHeight * $imageWidth);
            $imageHeight = $maxSize;
        }
    }
    if ($imageWidth > 0 && $imageHeight > 0) {
        if ($aspectRatio !== null) {
            $maxWidth = floor($imageHeight / ($aspectRatio[1] / $aspectRatio[0]));
            if ($maxWidth > $imageWidth) {
                $maxWidth = $imageWidth;
            }
            $maxHeight = $imageHeight;
            $containerStyle .= 'max-width:' . $maxWidth . 'px;max-height:' . $maxHeight . 'px;';
        } else {
            $containerStyle .= 'max-width:' . $imageWidth . 'px;max-height:' . $imageHeight . 'px;';
        }
        $versions = [];
        $isWebpSupported = $app->assets->isSupportedOutputType('webp');
        $addVersionURL = function ($width) use ($app, &$versions, $filename, $aspectRatio, $imageHeight, $quality, $isWebpSupported, &$originalURL) {
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
            if ($quality !== null) {
                $options['quality'] = $quality;
            }
            $url = $app->assets->getURL($filename, $options);
            $versions[] =  $url . ' ' . $width . 'w';
            $originalURL = $url;
            if ($isWebpSupported) {
                $options['outputType'] = 'webp';
                $versions[] = $app->assets->getURL($filename, $options) . ' ' . $width . 'w';
            }
        };
        if ($extension !== 'gif') {
            for ($width = 200; $width <= $imageWidth; $width += 200) {
                $addVersionURL($width);
            }
            if ($aspectRatio !== null) { // version for the max height
                $addVersionURL(floor($imageHeight / $aspectRatio[1] * $aspectRatio[0]));
            }
        }
        $addVersionURL($imageWidth); // version for the max width
        $versions = array_unique($versions);

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
$srcAttribute = isset($originalURL) ? ' src="' . htmlentities($originalURL) . '"' : '';
$dataSrcsetAttribute = isset($versions) ? ' data-srcset="' . htmlentities(implode(', ', $versions)) . '"' : '';

$class = (string) $component->class;
$classAttribute = isset($class[0]) ? ' class="' . htmlentities($class) . '"' : '';
$alt = (string) $component->alt;
$altAttribute = isset($alt[0]) ? ' alt="' . htmlentities($alt) . '"' : ' alt=""';
$title = (string) $component->title;
$titleAttribute = isset($title[0]) ? ' title="' . htmlentities($title) . '"' : '';

echo '<html>';

echo '<head><link rel="client-packages-embed" name="-ivopetkov-lazy-image-responsively-lazy"></head>';

echo '<body>';
echo '<span' . $classAttribute . ' style="' . $containerStyle . htmlentities($component->style) . '">';
echo '<span class="responsively-lazy"' . $attributes . ' style="' . $style . '">';
echo '<img ' . $altAttribute . $titleAttribute . $srcAttribute . $dataSrcsetAttribute . ' srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />';
echo '</span>';
echo '</span>';
echo '</body>';

echo '</html>';
