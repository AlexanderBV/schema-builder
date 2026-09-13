<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasOptions;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasValidationRules;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Enums\Alignment;
use Warrior\SchemaBuilder\Enums\ColumnType;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Enums\PaginationPosition;
use Warrior\SchemaBuilder\Enums\TabsPosition;

enum TestDummyEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuario Normal',
        };
    }
}

class DummyComponent
{
    use HasIdAndTitle;
    use HasOptions;
    use HasPermissions;
    use HasValidationRules;
    use HasVisibility;
    use Makeable;
}

class ConcernsAndEnumsTest extends TestCase
{
    public function test_enums_have_correct_values(): void
    {
        $this->assertSame('text', ColumnType::TEXT->value);
        $this->assertSame('avatar', ColumnType::AVATAR->value);
        $this->assertSame('badge', ColumnType::BADGE->value);
        $this->assertSame('currency', ColumnType::CURRENCY->value);

        $this->assertSame('text', FieldType::TEXT->value);
        $this->assertSame('select', FieldType::SELECT->value);
        $this->assertSame('file', FieldType::FILE->value);

        $this->assertSame('start', Alignment::START->value);
        $this->assertSame('center', Alignment::CENTER->value);
        $this->assertSame('end', Alignment::END->value);

        $this->assertSame('both', PaginationPosition::BOTH->value);
        $this->assertSame('toolbar', TabsPosition::TOOLBAR->value);
    }

    public function test_makeable_and_has_id_and_title(): void
    {
        $dummy = DummyComponent::make()
            ->id('user-id')
            ->title('User Title')
            ->subtitle('User Subtitle')
            ->description('User Description');

        $this->assertSame('user-id', $dummy->getId());
        $this->assertSame('User Title', $dummy->getTitle());
        $this->assertSame('User Title', $dummy->getLabel());
        $this->assertSame('User Subtitle', $dummy->getSubtitle());
        $this->assertSame('User Description', $dummy->getDescription());
    }

    public function test_has_visibility_conditionals(): void
    {
        $dummy = DummyComponent::make();
        $this->assertTrue($dummy->isVisible());

        $dummy->hidden();
        $this->assertFalse($dummy->isVisible());

        $dummy->visible();
        $this->assertTrue($dummy->isVisible());

        $dummy->when(true, function (DummyComponent $c) {
            $c->title('Modified by when');
        });
        $this->assertSame('Modified by when', $dummy->getTitle());

        $dummy->visibleWhen('role', 'admin');
        $this->assertSame([
            'field' => 'role',
            'is' => 'admin',
            'operator' => '===',
        ], $dummy->getVisibleWhen());
    }

    public function test_has_permissions(): void
    {
        $dummy = DummyComponent::make()->permission(['users.create', 'users.update']);
        $this->assertTrue($dummy->hasPermissions());
        $this->assertSame(['users.create', 'users.update'], $dummy->getPermissions());
        $this->assertSame('users.create', $dummy->getFirstPermission());

        $dummy2 = DummyComponent::make()->can('users.delete');
        $this->assertSame(['users.delete'], $dummy2->getPermissions());
    }

    public function test_has_options_array_and_enum(): void
    {
        $dummy = DummyComponent::make()->options([
            'a' => 'Opción A',
            'b' => 'Opción B',
        ]);

        $this->assertSame([
            ['value' => 'a', 'label' => 'Opción A'],
            ['value' => 'b', 'label' => 'Opción B'],
        ], $dummy->getOptions());

        $dummyEnum = DummyComponent::make()->optionsFromEnum(TestDummyEnum::class);
        $this->assertSame([
            ['value' => 'admin', 'label' => 'Administrador'],
            ['value' => 'user', 'label' => 'Usuario Normal'],
        ], $dummyEnum->getOptions());
    }

    public function test_has_validation_rules_and_patch_adaptation(): void
    {
        $dummy = DummyComponent::make()
            ->required()
            ->string()
            ->min(3)
            ->max(100)
            ->unique('users', 'email');

        $creationRules = $dummy->getValidationRules(isUpdate: false);
        $this->assertContains('required', $creationRules);
        $this->assertContains('string', $creationRules);
        $this->assertContains('min:3', $creationRules);
        $this->assertContains('max:100', $creationRules);
        $this->assertContains('unique:users,email', $creationRules);

        // Update mode (PATCH) -> required should become sometimes, required
        $updateRules = $dummy->getValidationRules(isUpdate: true);
        $this->assertSame(['sometimes', 'required', 'string', 'min:3', 'max:100', 'unique:users,email'], $updateRules);
    }
}
