<?php
namespace Pho\Http;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Respect\Validation\Exceptions\NestedValidationException;

class JsonController extends Controller
{
    public function setRequest(Request $request)
    {
        parent::setRequest($request);

        if ($request->getContentType() != 'json' || !$request->getContent()) {
            return;
        }

        $body = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Malformed JSON body !');
        }

        $this->body = new ParameterBag($body);
        $this->params = $request->query;
    }

    protected function jsonValue($key, $required = false, $default = null)
    {
        if (!$this->body->has($key)) {
            if (!$required) {
                return $default;
            }
            throw new HttpException(400, sprintf('JSON body does not have `%s` key !', $key));
        }

        return $this->body->get($key);
    }

    protected function validateBody($validatorClass, $method, $requiredKeys = [], $optionalKeys = [])
    {
        $validator = call_user_func_array([$validatorClass, 'validator'], [$method, $requiredKeys, $optionalKeys]);

        try {
            $validator->assert($this->body->all());
        } catch (NestedValidationException $exception) {
            $messages = implode(', ', $exception->getMessages());
            throw new HttpException(400, 'Malformed JSON body : '.$messages);
        }
    }
}
