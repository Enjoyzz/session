<?php

namespace Tests\Enjoys\Session;

use Enjoys\Session\Session;
use PHPUnit\Framework\TestCase;

new Session(
    null, [
            'gc_maxlifetime' => 10,
            'save_path' => __DIR__ . '/temp'
        ]
);

class SessionTest extends TestCase
{
    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                __DIR__ . '/temp',
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
    }

    public function test__construct()
    {
        $this->assertSame(\PHP_SESSION_ACTIVE, session_status());
        $session = new Session();
        $this->assertArrayHasKey('cookie_httponly', $session->getOptions());
    }

//    public function testSessionId()
//    {
//        $session = new Session();
//        $this->assertSame('', $session->getSessionId());
//    }

    public function testGetOptions()
    {
        $this->assertSame(
            '10',
            ini_get('session.gc_maxlifetime')
        );
    }

    public function testHas()
    {
        Session::set(['test' => 5]);
        $this->assertSame(true, Session::has('test'));
    }

    public function testDelete()
    {
        Session::set(['test' => 5]);
        $this->assertSame(true, Session::has('test'));
        Session::delete('test');
        $this->assertSame(false, Session::has('test'));
        $this->assertSame(true, Session::get('test', true));
    }

    public function testSetGet()
    {
        Session::set(['test' => 5]);
        $this->assertSame(5, Session::get('test'));
    }


}
