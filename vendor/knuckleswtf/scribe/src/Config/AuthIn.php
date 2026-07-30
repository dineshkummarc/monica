<?php

namespace Knuckles\Scribe\Config;

enum AuthIn: string
{
    case BEARER = 'bearer';
    case BASIC = 'basic';
    case HEADER = 'header';
    case QUERY = 'query';
    case BODY = 'body';
    case QUERY_OR_BODY = 'query_or_body';
}
