<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 2:02 PM
 */

namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\csp\AddCspHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class AddCspHeaderListenerTest extends TestCase
{

    public function testOnKernelResponse()
    {
        $subscriber = new AddCspHeaderListener('Content-Security-Policy-Report-Only', ['default-src' => ["'none'"], 'form-action' => ["'none'"], 'frame-ancestors' => ["'none'"] ]);

        $request = new Request([], [], []);
        $response = new Response('', 200, []);

        $event = $this->getMockBuilder(FilterResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('isMasterRequest')->willReturn(true);
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn($response);

        $this->assertInstanceOf(EventSubscriberInterface::class, $subscriber);

        $subscriber->onKernelResponse($event);

        $expected = ["default-src 'none'; form-action 'none'; frame-ancestors 'none';"];

        $this->assertEquals($expected, $response->headers->get('Content-Security-Policy-Report-Only', null, false));
    }

    public function testOnKernelResponseNonce()
    {
        $subscriber = new AddCspHeaderListener('Content-Security-Policy-Report-Only', ['default-src' => ["'none'"], 'form-action' => ["'none'"], 'frame-ancestors' => ["'none'"] ]);

        $request = new Request([], [], ['_nonce' => 'nonce']);
        $response = new Response('', 200, []);

        $event = $this->getMockBuilder(FilterResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('isMasterRequest')->willReturn(true);
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn($response);

        $this->assertInstanceOf(EventSubscriberInterface::class, $subscriber);

        $subscriber->onKernelResponse($event);

        $expected = ["default-src 'none'; form-action 'none'; frame-ancestors 'none'; script-src 'nonce-nonce';"];

        $this->assertEquals($expected, $response->headers->get('Content-Security-Policy-Report-Only', null, false));
    }


    public function testSubscribedEvents()
    {
        $this->assertEquals(array(KernelEvents::RESPONSE => 'onKernelResponse'), AddCspHeaderListener::getSubscribedEvents());
    }
    
}