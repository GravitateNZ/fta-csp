<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 5:53 PM
 */

namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\csp\Twig\CspNonceExtension;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;


class CspNonceExtensionTest extends TestCase
{

    /** @var Request */
    protected $request;

    /** @var CspNonceExtension */
    protected $extension;


    protected function setUp(): void
    {
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->extension = new CspNonceExtension($requestStack);
    }

    public function testNonce(): void
    {
        $nonce  = $this->extension->cspNonce();
        $this->assertNotNull($nonce);

        $this->assertEquals($nonce, $this->extension->cspNonce());

        $this->assertTrue($this->request->attributes->has('_nonce'));
        $this->assertEquals($nonce, $this->request->attributes->get('_nonce'));
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $this->assertInstanceOf(TwigFunction::class, $functions[0]);

        /** @var TwigFunction $function */
        $function = $functions[0];

        $this->assertEquals('nonce', $function->getName());
        $this->assertTrue(is_callable($function->getCallable()));

    }
}
