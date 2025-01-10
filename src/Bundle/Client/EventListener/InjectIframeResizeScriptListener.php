<?php

namespace Optime\Sso\Bundle\Client\EventListener;

use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

class InjectIframeResizeScriptListener
{
    public function __construct(
        private readonly Environment $twig,
        private readonly bool $injectIframeResizer,
    ) {
    }

    #[AsEventListener(priority: -10)]
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->injectIframeResizer) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($response->getStatusCode() !== 200) {
            return;
        }

        if (($response->headers->has('Content-Type') && !str_contains($response->headers->get('Content-Type') ?? '', 'html'))
            || 'html' !== $request->getRequestFormat()
            || false !== stripos($response->headers->get('Content-Disposition', ''), 'attachment;')
        ) {
            return;
        }

        $content = $response->getContent();

        if (!str_contains($content, '</body>')) {
            return;
        }

        $script = $this->twig->render('@OptimeSsoClient/_iframe_resize_script.html.twig');
        $content = str_replace('</body>', $script.'</body>', $content);

        $response->setContent($content);
    }
}