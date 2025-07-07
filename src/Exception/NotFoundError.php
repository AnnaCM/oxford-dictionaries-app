<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotFoundError extends NotFoundHttpException
{
    public function __construct(?string $message = '')
    {
        parent::__construct($message);
    }
}
