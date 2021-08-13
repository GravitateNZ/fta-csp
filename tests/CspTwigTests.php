<?php declare(strict_types=1);


namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\csp\CspHeaderListener;
use GravitateNZ\fta\csp\Twig\CspExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Cache\FilesystemCache;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;


class CspTwigTests extends TestCase
{

    protected HttpKernelInterface $kernel;
    protected Environment $environment;
    protected AbstractExtension $extension;

    protected function resetEnvironment(): void
    {
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $subscriber = new CspHeaderListener('Content-Security-Policy-Report-Only', ['default-src' => ["'none'"], 'form-action' => ["'none'"], 'frame-ancestors' => ["'none'"] ]);
        $this->extension = new CspExtension($subscriber);

        $this->environment = new Environment(new FilesystemLoader(__DIR__ . "/templates"));
        $this->environment->addExtension($this->extension);
    }

    protected function setUp(): void
    {
        $this->resetEnvironment();
    }


    public function testCspScriptToken(): void
    {
        $this->environment->render('csp-sha.twig');
        $this->assertStringContainsString(
        "'sha384-DzjSswPV+DRLYHDP7Sk9YQ1QeE3tgIbO9Q8PfIsj9uFTABu3jjkII/hSWRLkpcd5'",
            $this->extension->getListener()->getCspHeader()
        );
    }

    /**
     * @covers GravitateNZ\fta\csp\Twig\CspScriptTokenParser
     * @uses GravitateNZ\fta\csp\CspHeaderListener
     * @uses GravitateNZ\fta\csp\Twig\Extension
     */
    public function testCspScriptNestedToken(): void
    {
        $this->environment->render('csp-sha-nested.twig');
        $this->assertStringContainsString(
        "'sha384-ZM4q08TjXbPm9eyfCnlzqpnmRcpYfnSeyMss1JGK+I5+dkJPSpl1U/jqUE5+9ie9'",
            $this->extension->getListener()->getCspHeader()
        );
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @covers GravitateNZ\fta\csp\Twig\CspHashNode
     * @uses GravitateNZ\fta\csp\CspHeaderListener
     * @uses GravitateNZ\fta\csp\Twig\Extension
     */
    public function testInvalidTags(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessageMatches('/block does not look like a script or a style/');
        $this->environment->render('csp-sha-badtime.twig');
    }

    public function testNonce(): void
    {
        $s = $this->environment->render('csp-nonce.twig');
        $this->assertStringContainsString("nonce=\"{$this->extension->addCspNonce()}\"", $s);

    }

    public function testCaching()
    {

        $this->environment->setCache(new FilesystemCache(__DIR__ . "/cache"));

        $this->environment->render('csp-sha.twig');

        $this->assertStringContainsString(
            "'sha384-DzjSswPV+DRLYHDP7Sk9YQ1QeE3tgIbO9Q8PfIsj9uFTABu3jjkII/hSWRLkpcd5'",
            $this->extension->getListener()->getCspHeader()
        );

        $this->resetEnvironment();
        $this->environment->setCache(new FilesystemCache(__DIR__ . "/cache"));

        $this->environment->render('csp-sha.twig');

        $this->assertStringContainsString(
            "'sha384-DzjSswPV+DRLYHDP7Sk9YQ1QeE3tgIbO9Q8PfIsj9uFTABu3jjkII/hSWRLkpcd5'",
            $this->extension->getListener()->getCspHeader()
        );
    }
}