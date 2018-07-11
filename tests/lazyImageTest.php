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
class LazyImageTest extends BearFrameworkAddonTestCase
{

    /**
     * 
     */
    public function testOutput()
    {
        $app = $this->getApp();
        $this->createSampleFile($app->config->appDir . '/images/test.jpg', 'jpg');

        $app->assets->addDir($app->config->appDir . '/images/');

        $result = $app->components->process('<component src="lazy-image" filename="' . $app->config->appDir . '/images/test.jpg" />');
        $this->assertTrue(strpos($result, '/test.jpg') !== false);
        $this->assertTrue(strpos($result, 'srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $app->config->appDir . '/images/test.jpg" aspectRatio="1:1" />');
        $this->assertTrue(strpos($result, 'h70-w70/test.jpg') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $app->config->appDir . '/images/test.jpg" loadingBackground="checkered" />');
        $this->assertTrue(strpos($result, 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhE') !== false);

        $result = $app->components->process('<component src="lazy-image" filename="' . $app->config->appDir . '/images/test.jpg" loadingBackground="none" />');
        $this->assertTrue(strpos($result, 'background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhE') === false);
    }

}
