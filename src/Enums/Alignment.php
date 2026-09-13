<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Enums;

enum Alignment: string
{
    case START = 'start';
    case CENTER = 'center';
    case END = 'end';
}
