<?php
/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;

$app = App::get();

$appAssets = $app->assets;

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

$quality = (string)$component->getAttribute('quality');
$quality = strlen($quality) === 0 ? null : (int)$quality;

$fileWidth = (string)$component->getAttribute('filewidth');
$fileWidth = strlen($fileWidth) === 0 ? null : (int)$fileWidth;

$fileHeight = (string)$component->getAttribute('fileheight');
$fileHeight = strlen($fileHeight) === 0 ? null : (int)$fileHeight;

$containerStyle = 'display:inline-block;width:100%;overflow:hidden;';

$defaultURL = null;

$filename = (string) $component->filename;
if ($filename !== '') {
    if ($fileWidth !== null && $fileHeight !== null) {
        $imageWidth = $fileWidth;
        $imageHeight = $fileHeight;
    } else {
        $details = $appAssets->getDetails($filename, ['width', 'height']);
        $imageWidth = $details['width'];
        $imageHeight = $details['height'];
    }
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
        $isWebpSupported = $appAssets->isSupportedOutputType('webp');
        $addVersionURL = function ($width) use ($appAssets, &$versions, $filename, $extension, $aspectRatio, $imageHeight, $quality, $isWebpSupported, &$defaultURL) {
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
            $url = $appAssets->getURL($filename, $options);
            $versions[] =  $url . ' ' . $width . 'w';
            $defaultURL = $url;
            if ($extension !== 'gif') {
                if ($isWebpSupported) {
                    $options['outputType'] = 'webp';
                    $versions[] = $appAssets->getURL($filename, $options) . ' ' . $width . 'w webp';
                }
            }
        };
        if ($extension !== 'gif') {
            $widths = [50, 75, 100, 125, 150, 175, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650, 700, 750, 800, 850, 900, 950, 1000, 1100, 1200, 1300, 1400, 1500, 1700, 1900, 2100, 2500, 3000, 3500, 4000, 5000, 6000, 7000, 8000, 9000, 10000];
            foreach ($widths as $width) {
                if ($width <= $imageWidth) {
                    $addVersionURL($width);
                }
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

$imageContainerStyle = 'position:relative;height:0;display:block;';

$imageAttributes = '';

$class = (string) $component->class;
$classAttribute = isset($class[0]) ? ' class="' . htmlentities($class) . '"' : '';
$alt = (string) $component->alt;
$imageAttributes .= isset($alt[0]) ? ' alt="' . htmlentities($alt) . '"' : ' alt=""';
$title = (string) $component->title;
$imageAttributes .= isset($title[0]) ? ' title="' . htmlentities($title) . '"' : '';

$imageStyle = 'position:absolute;top:0;left:0;width:100%;height:100%;';
if ($aspectRatio !== null) {
    $imageContainerStyle .= 'padding-bottom:' . (number_format($aspectRatio[1] / $aspectRatio[0], 6, '.', '') * 100) . '%;';
}
$imageAttributes .= isset($defaultURL) ? ' src="' . htmlentities($defaultURL) . '"' : '';
$imageAttributes .= ' srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="';
$imageAttributes .= isset($versions) ? ' data-responsively-lazy="' . htmlentities(implode(', ', $versions)) . '"' : '';
$imageAttributes .= ' data-responsively-lazy-threshold="100%"';
if ($loadingBackground === 'checkered') {
    $imageAttributes .= ' data-on-responsively-lazy-load="this.parentNode.style.backgroundImage=\'none\';"';
    $imageContainerStyle .= 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUAQMAAAC3R49OAAAABlBMVEUAAAD///+l2Z/dAAAAAnRSTlMZGYn4zOAAAAAUSURBVAjXY2Sw38hIDP5/0IEYDADG0R1147/PtQAAAABJRU5ErkJggg==\');';
}

echo '<html>';

echo '<head><link rel="client-packages-embed" name="responsivelyLazy"></head>';

echo '<body>';
echo '<span ' . $classAttribute . ' style="' . $containerStyle . htmlentities($component->style) . '">';
echo '<span style="' . $imageContainerStyle . '">';
echo '<img ' . $imageAttributes . ' style="' . $imageStyle . '" />';
echo '</span>';
echo '</span>';
echo '</body>';

echo '</html>';
