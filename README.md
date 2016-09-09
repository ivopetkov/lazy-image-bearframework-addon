# Lazy Image
Addon for Bear Framework

This addon enables you to easily create lazy-loaded images that are SEO friendly. It's based on the popular library [Responsively Lazy](https://github.com/ivopetkov/responsively-lazy/). Multiple versions with different sizes are created on the fly and only the best one is loaded. This saves bandwidth and loads the website faster.

[![Build Status](https://travis-ci.org/ivopetkov/lazy-image-bearframework-addon.svg)](https://travis-ci.org/ivopetkov/lazy-image-bearframework-addon)
[![Latest Stable Version](https://poser.pugx.org/ivopetkov/lazy-image-bearframework-addon/v/stable)](https://packagist.org/packages/ivopetkov/lazy-image-bearframework-addon)
[![codecov.io](https://codecov.io/github/ivopetkov/lazy-image-bearframework-addon/coverage.svg?branch=master)](https://codecov.io/github/ivopetkov/lazy-image-bearframework-addon?branch=master)
[![License](https://poser.pugx.org/ivopetkov/lazy-image-bearframework-addon/license)](https://packagist.org/packages/ivopetkov/lazy-image-bearframework-addon)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/c3335b13bcfb42a2ab84c565debb308e)](https://www.codacy.com/app/ivo_2/lazy-image-bearframework-addon)

## Download and install

**Install via Composer**

```shell
composer require ivopetkov/lazy-image-bearframework-addon
```

**Download an archive**

Download the [latest release](https://github.com/ivopetkov/lazy-image-bearframework-addon/releases) from the [GitHub page](https://github.com/ivopetkov/lazy-image-bearframework-addon) and include the autoload file.
```php
include '/path/to/the/addon/autoload.php';
```

## Enable the addon
Enable the addon for your Bear Framework application.

```php
$app->addons->add('ivopetkov/lazy-image-bearframework-addon');
```


## Usage

```html
<component src="lazy-image" filename="path/to/the/file.jpg" />
```

### Attributes

`filename`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the file to be shown. It must be in a publicly accessible directory.

`aspectRatio`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The proportional relationship between the width and the height of the image. It is useful for cropping and resizing the image. Example values: 1:1, 1:2, 1.5:1, etc.

`class`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML class attribute value

`style`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML style attribute value

`alt`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML alt attribute value

`title`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML title attribute value

### Examples

The image rendered is a square
```html
<component src="lazy-image" filename="path/to/the/file.jpg" aspectRatio="1:1" />
```

A custom class name is added to the image HTML code
```html
<component src="lazy-image" filename="path/to/the/file.jpg" class="my-class-name" />
```

A title is added
```html
<component src="lazy-image" filename="path/to/the/file.jpg" title="New Year's Eve" />
```

## License
Lazy image addon for Bear Framework is open-sourced software. It's free to use under the MIT license. See the [license file](https://github.com/ivopetkov/lazy-image-bearframework-addon/blob/master/LICENSE) for more information.

## Author
This addon is created by Ivo Petkov. Feel free to contact me at [@IvoPetkovCom](https://twitter.com/IvoPetkovCom) or [ivopetkov.com](https://ivopetkov.com).
