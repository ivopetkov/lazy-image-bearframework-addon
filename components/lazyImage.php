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
$temp = (string) $component->getAttribute('aspect-ratio');
if (preg_match('/^[0-9\.]+:[0-9\.]+$/', $temp) === 1) {
    $temp = explode(':', $temp);
    $aspectRatio = [(float) $temp[0], (float) $temp[1]];
}
unset($temp);

$loadingBackground = 'none'; // checkered
$loadingBackgroundIsColor = false;
$loadingBackgroundIsAttribute = false;
$temp = (string) $component->getAttribute('loading-background');
if ($temp !== '') {
    if (array_search($temp, ['checkered', 'none']) !== false) {
        $loadingBackground = $temp;
    } else if (strpos($temp, 'color:') === 0) {
        $loadingBackgroundIsColor = true;
        $loadingBackground = substr($temp, 6);
    } else if (strpos($temp, 'attribute:') === 0) {
        $loadingBackgroundIsAttribute = true;
        $loadingBackground = substr($temp, 10);
    }
}

$minAssetWidth = (int)$component->getAttribute('min-asset-width');
if ($minAssetWidth === 0) {
    $minAssetWidth = null;
}
$minAssetHeight = (int)$component->getAttribute('min-asset-height');
if ($minAssetHeight === 0) {
    $minAssetHeight = null;
}
$maxAssetWidth = (int)$component->getAttribute('max-asset-width');
if ($maxAssetWidth === 0) {
    $maxAssetWidth = null;
}
$maxAssetHeight = (int)$component->getAttribute('max-asset-height');
if ($maxAssetHeight === 0) {
    $maxAssetHeight = null;
}

$fileWidth = (string)$component->getAttribute('file-width');
$fileWidth = $fileWidth === '' ? null : (int)$fileWidth;

$fileHeight = (string)$component->getAttribute('file-height');
$fileHeight = $fileHeight === '' ? null : (int)$fileHeight;

$lazyThreshold = (string)$component->getAttribute('lazy-threshold');
$lazyThreshold = $lazyThreshold === '' ? '300%' : $lazyThreshold;

$supportedAssetOptionsAttributes = [
    'cacheMaxAge' => ['asset-cache-max-age', 'int'],
    'quality' => ['asset-quality', 'int'],
    'svgFill' => ['asset-svg-fill', 'string'],
    'svgStroke' => ['asset-svg-stroke', 'string']
];

$assetOptions = [];
foreach ($supportedAssetOptionsAttributes as $assetOptionName => $assetOptionAttributeData) {
    $assetOptionAttributeName = $assetOptionAttributeData[0];
    $assetOptionAttributeValue = (string)$component->getAttribute($assetOptionAttributeName);
    if ($assetOptionAttributeValue !== '') {
        if ($assetOptionAttributeData[1] === 'int') {
            $assetOptionAttributeValue = (int)$assetOptionAttributeValue;
        }
        $assetOptions[$assetOptionName] = $assetOptionAttributeValue;
    }
}

$containerStyle = 'display:inline-block;width:100%;overflow:hidden;';

$defaultURL = null;

$filename = (string) $component->getAttribute('filename');
if ($filename !== '') {
    if ($fileWidth === null || $fileHeight === null) {
        $details = $appAssets->getDetails($filename, ['width', 'height']);
        $fileWidth = (int)$details['width'];
        $fileHeight = (int)$details['height'];
    }
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($fileWidth > 0 && $fileHeight > 0) {
        if ($aspectRatio !== null) {
            $maxWidth = floor($fileHeight / ($aspectRatio[1] / $aspectRatio[0]));
            if ($maxWidth > $fileWidth) {
                $maxWidth = $fileWidth;
            }
            $maxHeight = $fileHeight;
        } else {
            $maxWidth = $fileWidth;
            $maxHeight = $fileHeight;
        }
        if ($extension !== 'svg') {
            $containerStyle = str_replace('width:100%;', '', $containerStyle) . 'width:' . $maxWidth . 'px;max-width:100%;max-height:' . $maxHeight . 'px;';
        }
        $versions = [];
        $addVersionURL = function (?int $width, ?int $height, int $fileWidth, array $outputTypes) use ($appAssets, &$versions, $filename, &$defaultURL, $assetOptions, $extension): void {
            $key = $width . '-' . $height;
            if (isset($versions[$key])) {
                return;
            }
            $options = $assetOptions;
            if ($width !== null) {
                if ($height === null && $width === $fileWidth) {
                    // skip to optimize the URL
                } else {
                    $options['width'] = $width;
                }
            }
            if ($height !== null) {
                $options['height'] = $height;
            }
            $url = $appAssets->getURL($filename, $options);
            $defaultURL = $url; // Last added version will be the the default one
            if ($width !== null) {
                foreach ($outputTypes as $outputType) {
                    $options['outputType'] = $outputType;
                    $versions[$key . '-' . $outputType] = $appAssets->getURL($filename, $options) . ' ' . $width . 'w ' . $outputType;
                }
                if (array_search($extension, $outputTypes) === false) {
                    $versions[$key] =  $url . ' ' . $width . 'w';
                }
            }
        };
        if ($extension === 'gif' || $extension === 'svg') {
            $addVersionURL(null, null, $fileWidth, []);
        } else {
            $outputTypes = [];
            if ($appAssets->isSupportedOutputType('webp')) {
                $outputTypes[] = 'webp'; // Add always even when extension is webp. Must be before PNG.
            }
            if ($appAssets->isSupportedOutputType('avif')) {
                if ($extension === 'avif') { // Don't enable for all yet
                    $outputTypes[] = 'avif'; // Add always even when extension is avif. Must be before PNG.
                }
            }
            if ($extension === 'webp' || $extension === 'avif') {
                $outputTypes[] = 'png'; // Fallback for old browsers
            }
            $calculateAspectRatioValues = function (int $width) use ($aspectRatio, $fileHeight) {
                $height = (int) ($width * $aspectRatio[1] / $aspectRatio[0]);
                if ($height > $fileHeight) {
                    $newWidth = floor($fileHeight / $aspectRatio[1] * $aspectRatio[0]);
                    if ($newWidth < 1) {
                        $newWidth = 1;
                    }
                    return [$newWidth, $fileHeight];
                }
                if ($height < 1) {
                    $height = 1;
                }
                return [$width, $height];
            };
            $widths = [50, 75, 100, 125, 150, 175, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650, 700, 750, 800, 850, 900, 950, 1000, 1100, 1200, 1300, 1400, 1500, 1700, 1900, 2100, 2500, 3000, 3500, 4000, 5000, 6000, 7000, 8000, 9000, 10000, $fileWidth];
            $addWidthForHeight = function (int $height, array $aspectRatio) use (&$widths): void {
                $width = floor($height / $aspectRatio[1] * $aspectRatio[0]);
                if ($width < 1) {
                    $width = 1;
                }
                $widths[] = $width;
            };
            if ($aspectRatio !== null) { // Version for the max file height
                $addWidthForHeight($fileHeight, $aspectRatio);
            }
            if ($minAssetHeight !== null) { // Version for the min image height
                $addWidthForHeight($minAssetHeight, $aspectRatio !== null ? $aspectRatio : [$fileWidth, $fileHeight]);
            }
            if ($maxAssetHeight !== null) { // Version for the max image height
                $addWidthForHeight($maxAssetHeight, $aspectRatio !== null ? $aspectRatio : [$fileWidth, $fileHeight]);
            }
            if ($minAssetWidth !== null) { // Version for the min image width
                $widths[] = $minAssetWidth;
            }
            if ($maxAssetWidth !== null) { // Version for the max image width
                $widths[] = $maxAssetWidth;
            }
            sort($widths);
            foreach ($widths as $width) {
                if ($width > $fileWidth) {
                    continue;
                }
                if ($maxAssetWidth !== null && $width > $maxAssetWidth) {
                    continue;
                }
                if ($minAssetWidth !== null && $width < $minAssetWidth) {
                    continue;
                }
                if ($aspectRatio !== null) {
                    list($versionWidth, $versionHeight) = $calculateAspectRatioValues($width);
                } else {
                    $versionWidth = $width;
                    $versionHeight = null;
                }
                if ($minAssetHeight !== null || $maxAssetHeight !== null) {
                    $height = $versionHeight !== null ? $versionHeight : floor($width / $fileWidth * $fileWidth);
                    if ($minAssetHeight !== null) {
                        if ($height < $minAssetHeight) {
                            continue;
                        }
                    } else {
                        if ($height > $maxAssetHeight) {
                            continue;
                        }
                    }
                }
                $addVersionURL($versionWidth, $versionHeight, $fileWidth, $outputTypes);
            }
        }

        if ($aspectRatio === null) {
            $aspectRatio = [$fileWidth, $fileHeight];
        }
    }
}

$imageContainerStyle = 'height:0;display:block;'; //position:relative;
$imageContainerAttributes = '';

$imageAttributes = '';

$class = (string) $component->getAttribute('class');
$classAttribute = isset($class[0]) ? ' class="' . htmlentities($class) . '"' : '';
$alt = (string) $component->getAttribute('alt');
$imageAttributes .= isset($alt[0]) ? ' alt="' . htmlentities($alt) . '"' : ' alt=""';
$title = (string) $component->getAttribute('title');
$imageAttributes .= isset($title[0]) ? ' title="' . htmlentities($title) . '"' : '';

$imageStyle = 'width:100%;'; //position:absolute;top:0;left:0;height:100%;
if ($aspectRatio !== null) {
    $imageContainerStyle .= 'padding-bottom:' . (number_format($aspectRatio[1] / $aspectRatio[0], 6, '.', '') * 100) . '%;';
}
$imageAttributes .= isset($defaultURL) ? ' src="' . htmlentities($defaultURL) . '"' : '';
$imageAttributes .= ' srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="';
$imageAttributes .= isset($versions) ? ' data-responsively-lazy="' . htmlentities(implode(', ', $versions)) . '"' : '';
$imageAttributes .= ' data-responsively-lazy-threshold="' . $lazyThreshold . '"';
if ($loadingBackground === 'checkered') {
    $imageAttributes .= ' data-on-responsively-lazy-load="this.parentNode.style.backgroundImage=\'none\';"';
    $imageContainerStyle .= 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUAQMAAAC3R49OAAAABlBMVEUAAAD///+l2Z/dAAAAAnRSTlMZGYn4zOAAAAAUSURBVAjXY2Sw38hIDP5/0IEYDADG0R1147/PtQAAAABJRU5ErkJggg==\');';
} elseif ($loadingBackgroundIsColor) {
    $imageAttributes .= ' data-on-responsively-lazy-load="this.parentNode.style.backgroundColor=\'none\';"';
    $imageContainerStyle .= 'background-color:' . $loadingBackground . ';';
} elseif ($loadingBackgroundIsAttribute) {
    $imageAttributes .= ' data-on-responsively-lazy-load="this.parentNode.removeAttribute(\'' . htmlentities($loadingBackground) . '\');"';
    $imageContainerAttributes .= ' ' . htmlentities($loadingBackground) . '=""';
}

echo '<html>';

echo '<head><link rel="client-packages-embed" name="responsivelyLazy"></head>';

echo '<body>';
echo '<span ' . $classAttribute . ' style="' . $containerStyle . htmlentities((string)$component->getAttribute('style')) . '">';
echo '<span style="' . $imageContainerStyle . '"' . $imageContainerAttributes . '>';
echo '<img ' . $imageAttributes . ' style="' . $imageStyle . '" />';
echo '</span>';
echo '</span>';
echo '</body>';

echo '</html>';
