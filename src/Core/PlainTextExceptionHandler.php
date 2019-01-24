<?php
namespace Pho\Core;

use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;

class PlainTextExceptionHandler extends ExceptionHandler {
    public function getHtml($exception) {
        $html = parent::getHtml($exception);
        $pos1 = strpos($html, '<body>');
        $pos2 = strpos($html, '</body>');

        $stripped = strip_tags(substr($html, $pos1 + 6, $pos2 - $pos1 - 6));
        $trimmed = trim(preg_replace('/ +/', ' ', $stripped));

        return preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n\n", $trimmed);
    }
}