<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Enums;

enum PaginationPosition: string
{
    case BOTH = 'both';
    case TOP = 'top';
    case BOTTOM = 'bottom';
    case NONE = 'none';
}
