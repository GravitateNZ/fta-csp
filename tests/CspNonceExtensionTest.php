<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 5:53 PM
 */

namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\csp\CspHeaderListener;
use GravitateNZ\fta\csp\Twig\CspExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;


/**
 * @covers GravitateNZ\fta\csp\Twig\CspExtension
 * @uses GravitateNZ\fta\csp\CspHeaderListener
 */
class CspNonceExtensionTest extends TestCase
{

    /** @var Request */
    protected $request;

    /** @var CspExtension */
    protected $extension;


    protected function setUp(): void
    {
        $subscriber = new CspHeaderListener('Content-Security-Policy-Report-Only', ['default-src' => ["'none'"], 'form-action' => ["'none'"], 'frame-ancestors' => ["'none'"] ]);
        $this->extension = new CspExtension($subscriber);
    }

    public function testNonce(): void
    {
        $nonce  = $this->extension->addCspNonce();
        $this->assertNotNull($nonce);
        $this->assertEquals($nonce, $this->extension->addCspNonce());

        $h = $this->extension->getListener()->getCspHeader();

        $this->assertStringContainsString("nonce-{$nonce}", $h);

    }
}
