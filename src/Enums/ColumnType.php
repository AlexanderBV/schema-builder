<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Enums;

enum ColumnType: string
{
    case TEXT = 'text';
    case AVATAR = 'avatar';
    case BADGE = 'badge';
    case CURRENCY = 'currency';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case BOOLEAN = 'boolean';
    case LINK = 'link';
    case JSON = 'json';
}
