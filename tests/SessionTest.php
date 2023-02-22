<?php

namespace Tests\Enjoys\Session;


use Enjoys\Session\Handler\SecureHandler;
use Enjoys\Session\Session;
use Exception;
use PHPUnit\Framework\TestCase;

use function random_int;

use const PHP_SESSION_ACTIVE;

new Session(
    new SecureHandler(),
    [
        'gc_maxlifetime' => 10,
        'save_path' => __DIR__ . '/_sessions'
    ]
);

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SessionTest extends TestCase
{
    /**
     * @var array<string,mixed>
     */
    private array $test = [];
    private Session $session;


    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->test = ['test' => random_int(0, 1000)];
        $this->session = new Session(
            new SecureHandler(),
            [
                'gc_maxlifetime' => 10,
                'save_path' => __DIR__ . '/_sessions'
            ]
        );
    }

    protected function tearDown(): void
    {
        session_gc();
    }

    public function test__construct(): void
    {
        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
    }


    public function testSetOptions(): void
    {
        $this->assertSame(
            '10',
            ini_get('session.gc_maxlifetime')
        );

        $this->assertSame(__DIR__ . '/_sessions', session_save_path());
    }

    public function testHas(): void
    {
        $this->session->set($this->test);
        $this->assertSame(true, $this->session->has('test'));
        $this->assertSame($this->test['test'], $_SESSION['test']);
    }

    public function testClear(): void
    {
        $this->session->set($this->test);
        $this->assertSame($this->test['test'], $this->session->get('test'));
        $this->assertSame($this->test['test'], $_SESSION['test']);

        $this->session->clear();
        $this->assertSame([], $this->session->getData());
        $this->assertSame([], $_SESSION);
    }

    public function testDelete(): void
    {
        $this->session->set($this->test);
        $this->assertSame(true, $this->session->has('test'));
        $this->assertSame($this->test, $_SESSION);
        $this->session->delete('test');
        $this->assertSame([], $_SESSION);
        $this->assertSame(false, $this->session->has('test'));
        $this->assertSame(true, $this->session->get('test', true));
    }

    public function testSet(): void
    {
        $this->session->set($this->test);
        $this->assertSame($this->test['test'], $this->session->get('test'));
        $this->assertSame($this->test['test'], $_SESSION['test']);
    }


}
