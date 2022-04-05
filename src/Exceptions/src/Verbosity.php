<?php

namespace Spiral\Exceptions;

enum Verbosity: int
{
    case BASIC = 0;
    case VERBOSE = 1;
    case DEBUG = 2;
}
