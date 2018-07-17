<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 1:31 PM
 */

namespace GravitateNZ\fta\csp\Twig;


use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CspNonceExtension extends AbstractExtension
{

    protected $nonce;
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('nonce', [$this, 'cspNonce']),
        ];
    }

    /**
     * returns a nonce that can be used for csp headers.
     * the nonce is also added to the master request so it can be added to csp headers
     * @return string
     * @throws \Exception
     */
    public function cspNonce()
    {
        //generate a nonce, return it and stuff it into a page...
        if (!$this->nonce) {
            $this->nonce = base64_encode(random_bytes(32));
            $this->requestStack->getMasterRequest()->attributes->set('_nonce', $this->nonce);
        }
        return $this->nonce;
    }

}