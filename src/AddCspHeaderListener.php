<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 1:48 PM
 */

namespace GravitateNZ\fta\csp;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class AddCspHeaderListener implements EventSubscriberInterface
{

    protected $cspHeader;
    protected $cspOptions;

    public function __construct(string $cspHeader, array $cspOptions)
    {
        $this->cspHeader = $cspHeader;
        $this->cspOptions = $cspOptions;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->has('_nonce')) {
            $nonce = $request->attributes->get('_nonce');

            if (!isset($this->cspOptions['script-src'])) {
                $this->cspOptions['script-src'] = [];//this must always be in place incase we have nonce.
            }

            $this->cspOptions['script-src'][]  = "'nonce-$nonce'";
        }

        $headers = implode("; ", \functional\reduce_left($this->cspOptions, function ($value, $index, $collection, $reduction) {
                $reduction[] = $index . " " . implode(" ", $value);
                return $reduction;
        }, [])) . ";";

        $event->getResponse()->headers->set($this->cspHeader, $headers, false);
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

}