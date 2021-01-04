<?php

namespace Tests\Enjoys\Session;


use Enjoys\Session\Handler\SecureHandler;
use Enjoys\Session\Session;
use PHPUnit\Framework\TestCase;

new Session(
    new SecureHandler(),
    [
        'gc_maxlifetime' => 10,
        'save_path' => __DIR__ . '/_sessions'
    ]
);

class SessionTest extends TestCase
{
    private array $test = [];
    private Session $session;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->test = ['test' => \random_int(0, 1000)];
        $this->session = new Session();
    }

    protected function tearDown(): void
    {
        session_gc();
    }

    public function test__construct()
    {
        $this->assertSame(\PHP_SESSION_ACTIVE, session_status());
//        $session = new Session();
//        $this->assertArrayHasKey('cookie_httponly', $session->getOptions());
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
        $this->session->set($this->test);
        $this->assertSame(true, $this->session->has('test'));
    }

//    public function testClear()
//    {
//        $this->session->clear();
//        $this->assertSame(null, $_SESSION);
//    }

    public function testDelete()
    {
        $this->session->set($this->test);
        $this->assertSame(true, $this->session->has('test'));
        $this->assertSame($this->test, $_SESSION);
        $this->session->delete('test');
        $this->assertSame([], $_SESSION);
        $this->assertSame(false, $this->session->has('test'));
        $this->assertSame(true, $this->session->get('test', true));
    }

    public function testSetGet()
    {
        $this->session->set($this->test);
        $this->assertSame($this->test['test'], $this->session->get('test'));
    }




}
