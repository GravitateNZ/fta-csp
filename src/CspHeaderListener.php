<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 1:48 PM
 */

namespace GravitateNZ\fta\csp;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CspHeaderListener implements EventSubscriberInterface
{

    protected string $cspHeader;
    protected array $cspOptions;

    public function __construct(string $cspHeader = '', array $cspOptions = [])
    {
        $this->cspHeader = $cspHeader;
        $this->cspOptions = $cspOptions;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->cspHeader) {
            return;
        }

        if (!$event->isMasterRequest()) {
            return;
        }

        $event->getResponse()->headers->set(
            $this->cspHeader,
            $this->getCspHeader(),
            false
        );
    }

    public function getCspHeader(): string
    {
        return implode("; ", \functional\reduce_left($this->cspOptions, function ($value, $index, $collection, $reduction) {
                $reduction[] = $index . " " . implode(" ", $value);
                return $reduction;
            }, [])) . ";";
    }

    public function addCspDirective(string $directive, string $value): void
    {
        if (!$this->cspHeader) {
            return;
        }

        $this->cspOptions[$directive][] ??= $value;

    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

}
