<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 1:31 PM
 */

namespace GravitateNZ\fta\csp\Twig;


use GravitateNZ\fta\csp\CspHeaderListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CspExtension extends AbstractExtension
{

    protected $nonce;
    protected CspHeaderListener $listener;

    public function __construct(CspHeaderListener $listener)
    {
        $this->listener = $listener;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nonce', [$this, 'addCspNonce']),
            new TwigFunction('cspDirective', [$this, 'addCspDirective']),
            new TwigFunction('cspHash', [$this, 'addCspHash']),
        ];
    }

    public function getTokenParsers(): array
    {
        return [
            new CspScriptTokenParser($this),
        ];
    }

    /**
     * returns a nonce that can be used for csp headers.
     * the nonce is also added to the master request so it can be added to csp headers
     * @return string
     * @throws \Exception
     */
    public function addCspNonce(): string
    {
        //generate a nonce, return it and stuff it into a page...
        if (!$this->nonce) {
            $this->nonce = base64_encode(random_bytes(32));
            $this->listener->addCspDirective('script-src', "nonce-{$this->nonce}");
        }
        return $this->nonce;
    }

    public function addCspHash(string $body, string $algo = 'sha384'): void
    {
        $r = preg_match('/^\s*+<(?<type>script|style)[^>]*+>(?<body>(?s)(.*?))<\/(\1)>\s*?$/m', $body, $matches);
        if (!$r) {
            throw new \RuntimeException("block does not look like a script or a style");
        }

        $hash = base64_encode(hash($algo, $matches['body'], true));

        $this->addCspDirective(
            "{$matches['type']}-src",
            "'$algo-$hash'"
        );
    }

    public function addCspDirective(string $directive, string $value): void
    {
        $this->listener->addCspDirective($directive, $value);
    }

    public function getListener(): CspHeaderListener
    {
        return $this->listener;
    }
}