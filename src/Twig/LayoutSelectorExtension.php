<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class LayoutSelectorExtension extends AbstractExtension implements GlobalsInterface
{
    public RequestStack $requestStack;

    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    public function getGlobals(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $uri = $request ? $request->getRequestUri() : '';
        $segments = explode('/', trim($uri, '/'));
        $section = $segments[0] ?? '';

        switch ($section) {
            case 'definitions':
            case 'translations':
                $layout = $section . '/index.html.twig';
                break;
            default:
                $layout = 'base.html.twig';
        }

        return ['layout_template' => $layout];
    }
}
