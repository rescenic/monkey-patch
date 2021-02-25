<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use CIPHPUnitTest;
use CIPHPUnitTestReflection;
use TestCase;

use function file_exists;
use function unlink;

/**
 * @group ci-phpunit-test
 * @group patcher
 */
class MonkeyPatchManager_test extends TestCase
{
    private static $debug;
    private static $log_file;

    public static function setUpBeforeClass(): void
    {
        self::$debug = CIPHPUnitTestReflection::getPrivateProperty('MonkeyPatchManager', 'debug');
        self::$log_file = CIPHPUnitTestReflection::getPrivateProperty('MonkeyPatchManager', 'log_file');
    }

    public static function tearDownAfterClass(): void
    {
        Cache::clearCache();
        CIPHPUnitTest::setPatcherCacheDir();
        CIPHPUnitTestReflection::setPrivateProperty('MonkeyPatchManager', 'debug', self::$debug);
        CIPHPUnitTestReflection::setPrivateProperty('MonkeyPatchManager', 'log_file', self::$log_file);

        unlink(__DIR__ . '/monkey-patch-debug.log');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Failed to create folder:
     */
    public function test_setCacheDir_error(): void
    {
        MonkeyPatchManager::setCacheDir(null);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Can't read 'dummy'
     */
    public function test_patch_error_cannot_read_file(): void
    {
        MonkeyPatchManager::patch('dummy');
    }

    public function test_patch_miss_cache(): void
    {
        $cache_dir = APPPATH . 'tests/_ci_phpunit_test/tmp/cache_test';
        CIPHPUnitTest::setPatcherCacheDir($cache_dir);

        $cache_file = Cache::getSrcCacheFilePath(__FILE__);
        $this->assertFalse(file_exists($cache_file));

        MonkeyPatchManager::patch(__FILE__);

        $this->assertTrue(file_exists($cache_file));
    }

    public function test_log_file_path_configurable(): void
    {
        $debug_method = CIPHPUnitTestReflection::getPrivateMethodInvoker('MonkeyPatchManager', 'setDebug');
        $debug_method(['debug' => true, 'log_file' => __DIR__ . '/monkey-patch-debug.log']);

        $actual_debug = CIPHPUnitTestReflection::getPrivateProperty('MonkeyPatchManager', 'debug');
        $actual_log_file = CIPHPUnitTestReflection::getPrivateProperty('MonkeyPatchManager', 'log_file');
        $this->assertTrue($actual_debug);
        $this->assertEquals(__DIR__ . '/monkey-patch-debug.log', $actual_log_file);
    }
}
