<?php

namespace App\Structs;

class ResponseCode {
    const SUCCESS = 200;
    const UPSERT = 201;
    const NOT_FOUND = 404;
    const NOT_AUTHENTICATED = 401;
    const NOT_ALLOWED = 403;
    const BAD_REQUEST = 400;
    const DELETED = 410;
}
