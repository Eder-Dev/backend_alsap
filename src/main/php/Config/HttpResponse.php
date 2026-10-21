<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Config;

final class HttpResponse
{
    public function __construct(public readonly mixed $body, public readonly int $status = 200)
    {
    }
}