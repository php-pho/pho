<?php
namespace Pho\Http;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class Controller
{
    protected $container;
    protected $request;
    protected $body;
    protected $params;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->body = $request->request;
        $this->params = $request->query;
    }

    protected function get($keyName)
    {
        return $this->container->get($keyName);
    }

    protected function json($data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        return new JsonResponse($data, $status, $headers, $json);
    }

    protected function text($content = null, int $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    protected function redirect(string $endpoint, int $status = 302, array $headers = [])
    {
        return new RedirectResponse($endpoint, $status, $headers);
    }

    protected function redirectFor(string $named_route, array $params = [], int $status = 302, array $headers = [])
    {
        $endpoint = $this->container->get(UrlGeneratorInterface::class)->generate($named_route, $params);

        return $this->redirect($endpoint, $status, $headers);
    }

    public function redirectWithFlash(string $named_route, array $params = [], string $flash_type, string $flash_content)
    {
        $this->addFlashMessage($flash_type, $flash_content);

        return $this->redirectFor($named_route, $params);
    }

    public function render(string $template, array $data = [], int $status = 200, array $headers = [])
    {
        $content = $this->container->get('twig')->render($template, $data);

        return new Response($content, $status, $headers);
    }

    public function addFlashMessage(string $flash_type, string $flash_content)
    {
        $this->request->getSession()->getFlashBag()->add('message', [
            'type' => $flash_type,
            'content' => $flash_content,
        ]);

        return $this;
    }

    public function getPostData(string $field = null, $default = null)
    {
        return empty($field) ? $this->body->all() : $this->body->get($field, $default);
    }

    public function getQueryParam(string $field = null, $default = null)
    {
        return empty($field) ? $this->params->all() : $this->params->get($field, $default);
    }

    protected function validateBody($validatorClass, string $method, array $requiredKeys = [], array $optionalKeys = [])
    {
        $validator = call_user_func_array([$validatorClass, 'validator'], [$method, $requiredKeys, $optionalKeys]);
        $validator->assert($this->body->all());
    }
}
