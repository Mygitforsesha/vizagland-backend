<?php

namespace App\Constants;

final class HttpStatus
{
    public const OK = 200;

    public const CREATED = 201;

    public const NO_CONTENT = 204;

    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const UNPROCESSABLE_ENTITY = 422;

    public const SERVICE_UNAVAILABLE = 503;

    public const INTERNAL_SERVER_ERROR = 500;
}
