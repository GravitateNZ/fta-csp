<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 2:02 PM
 */

namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\csp\CspHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers GravitateNZ\fta\csp\CspHeaderListener
 */
class AddCspHeaderListenerTest extends TestCase
{

    protected HttpKernelInterface $kernel;

    public function setUp(): void
    {
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
    }

    public function testOnKernelResponse()
    {
        $subscriber = new CspHeaderListener('Content-Security-Policy-Report-Only', ['default-src' => ["'none'"], 'form-action' => ["'none'"], 'frame-ancestors' => ["'none'"] ]);

        $request = new Request([], [], []);
        $response = new Response('', 200, []);
        $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $this->assertInstanceOf(EventSubscriberInterface::class, $subscriber);

        $subscriber->onKernelResponse($event);

        $expected = "default-src 'none'; form-action 'none'; frame-ancestors 'none';";

        $this->assertEquals($expected, $response->headers->get('Content-Security-Policy-Report-Only', null, false));
    }

    public function testOnKernelResponseNonce()
    {
        $subscriber = new CspHeaderListener(
            'Content-Security-Policy-Report-Only',
            [
                'default-src' => ["'none'"],
                'form-action' => ["'none'"],
                'frame-ancestors' => ["'none'"],
            ]
        );

        $subscriber->addCspDirective('script-src', "'nonce-nonce'");
        $request = new Request([], [], []);
        $response = new Response('', 200, []);
        $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $this->assertInstanceOf(EventSubscriberInterface::class, $subscriber);

        $subscriber->onKernelResponse($event);

        $expected = "default-src 'none'; form-action 'none'; frame-ancestors 'none'; script-src 'nonce-nonce';";

        $h = $response->headers->get('Content-Security-Policy-Report-Only', null, false);

        $this->assertEquals($expected, $h);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(array(KernelEvents::RESPONSE => 'onKernelResponse'), CspHeaderListener::getSubscribedEvents());
    }

}
