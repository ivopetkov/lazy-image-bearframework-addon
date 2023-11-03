<?php

/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class LazyImageTest extends BearFramework\AddonTests\PHPUnitTestCase
{

    /**
     * 
     */
    public function testOutput()
    {
        $app = $this->getApp();

        $tempDir = $this->getTempDir();
        $this->makeSampleFile($tempDir . '/images/test.jpg', 'jpg');

        $app->assets->addDir($tempDir . '/images/');

        $result = $app->components->process('<component src="lazy-image" filename="' . $tempDir . '/images/test.jpg" />');
        $this->assertTrue(strpos($result, '/test.jpg') !== false);
        $this->assertTrue(strpos($result, 'srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $tempDir . '/images/test.jpg" aspect-ratio="1:1" />');
        $this->assertTrue(strpos($result, '-w70-h70-') !== false);
        $this->assertTrue(strpos($result, '/test.jpg') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $tempDir . '/images/test.jpg" loading-background="checkered" />');
        $this->assertTrue(strpos($result, 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhE') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $tempDir . '/images/test.jpg" loading-background="none" />');
        $this->assertTrue(strpos($result, 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhE') === false);
    }
}
