<?php
namespace Pho\Http;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected $container;
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    protected function json($data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        return new JsonResponse($data, $status, $headers, $json);
    }

    protected function redirect($endpoint, int $status = 302, array $headers = [])
    {
        return new RedirectResponse($endpoint, $status, $headers);
    }

    public function render($template, array $data = [], int $status = 200, array $headers = [])
    {
        $content = $this->container->get('twig')->render($template, $data);

        return new Response($content, $status, $headers);
    }
}
