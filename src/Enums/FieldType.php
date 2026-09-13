<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Enums;

enum FieldType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case PASSWORD = 'password';
    case NUMBER = 'number';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case SWITCH = 'switch';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case DATE_RANGE = 'date_range';
    case FILE = 'file';
    case IMAGE = 'image';
    case HIDDEN = 'hidden';
}
