<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table\Formatters;

use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @phpstan-consistent-constructor
 */
class AvatarFormatter implements ColumnFormatterContract
{
    use Makeable;

    protected string $avatarKey;

    protected ?string $titleKey;

    protected ?string $subtitleKey;

    public function __construct(string $avatarKey = 'avatar', ?string $titleKey = null, ?string $subtitleKey = null)
    {
        $this->avatarKey = $avatarKey;
        $this->titleKey = $titleKey;
        $this->subtitleKey = $subtitleKey;
    }

    public function getType(): ColumnType
    {
        return ColumnType::AVATAR;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'avatarKey' => $this->avatarKey,
            'titleKey' => $this->titleKey,
            'subtitleKey' => $this->subtitleKey,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->getOptions();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
