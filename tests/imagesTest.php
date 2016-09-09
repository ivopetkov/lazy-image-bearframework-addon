<?php

/*
 * Lazy image addon for Bear Framework
 * https://github.com/ivopetkov/lazy-image-bearframework-addon
 * Copyright (c) 2016 Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class ImagesTest extends BearFrameworkAddonTestCase
{

    /**
     * 
     */
    public function testProccess()
    {
        $app = $this->getApp();
        $this->createSampleFile($app->config->appDir . '/images/test.jpg', 'jpg');

        $app->assets->addDir($app->config->appDir . '/images/');

        $content = '<!DOCTYPE html><html><head></head><body>content</body></html>';
        $result = $app->components->process('<component src="lazy-image" filename="' . $app->config->appDir . '/images/test.jpg" />');
        $this->assertTrue(strpos($result, '/test.jpg') !== false);
        $this->assertTrue(strpos($result, 'srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="') !== false);
    }

}
