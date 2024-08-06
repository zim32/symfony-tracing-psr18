<?php
declare(strict_types=1);

namespace Zim\SymfonyPsr18TracingBundle\Instrumentation\Psr18Client;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zim\SymfonyTracingCoreBundle\ScopedTracerInterface;

class InstrumentedPsr18Client implements ClientInterface
{
    public function __construct(
        private readonly ClientInterface $inner,
        private readonly ScopedTracerInterface $httpTracer,
        private readonly bool $propagate,
    )
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $spanName = sprintf('%s %s', $request->getMethod(), $request->getUri());
        $span = $this->httpTracer->startSpan($spanName, SpanKind::KIND_CLIENT);

        if ($this->propagate) {
            $carrier = [];
            TraceContextPropagator::getInstance()->inject($carrier);
            foreach ($carrier as $key => $value) {
                $request = $request->withHeader($key, $value);
            }
        }

        try {
            return $this->inner->sendRequest($request);
        } finally {
            $span->end();
        }
    }
}
