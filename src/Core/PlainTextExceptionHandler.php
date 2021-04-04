<?php

namespace Pho\Core;

use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class PlainTextExceptionHandler extends ErrorHandler
{
    private $debug;

    private $charset;

    private $fileLinkFormat;

    public function __construct(bool $debug = true, string $charset = null, $fileLinkFormat = null)
    {
        $this->debug = $debug;
        $this->charset = $charset ?: ini_get('default_charset') ?: 'UTF-8';
        $this->fileLinkFormat = $fileLinkFormat;
    }

    public function getHtml($exception)
    {
        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        switch ($exception->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }

        if (!$this->debug) {
            return $title;
        }

        $content = '';
        try {
            $count = \count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $ind = $count - $position + 1;
                $content .= sprintf("\n[%d/%d] %s : %s", $ind, $total, $e['class'], $e['message']);
                foreach ($e['trace'] as $trace) {
                    if ($trace['function']) {
                        $content .= sprintf("\n\t- at %s %s %s", $trace['class'], $trace['type'], $trace['function']);
                    }
                    if (isset($trace['file']) && isset($trace['line'])) {
                        $content .= sprintf(" in %s:%d", $trace['file'], $trace['line']);
                    }
                }
            }
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception anymore
            if ($this->debug) {
                $e = FlattenException::create($e);
                $title = sprintf('Exception thrown when handling an exception (%s: %s)', $e->getClass(), $e->getMessage());
            } else {
                $title = 'Whoops, looks like something went wrong.';
            }
            return $title;
        }
        return $content;
    }
}
