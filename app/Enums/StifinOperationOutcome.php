<?php

namespace App\Enums;

enum StifinOperationOutcome: string
{
    case Success = 'success';
    case Fail4xx = 'fail_4xx';
    case Fail5xx = 'fail_5xx';
    case AmbiguousTimeout = 'ambiguous_timeout';
    case AmbiguousTransport = 'ambiguous_transport';
    case Unparseable = 'unparseable';
    case PreflightError = 'preflight_error';
}
