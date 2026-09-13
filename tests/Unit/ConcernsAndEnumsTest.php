<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
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

enum TestDummyRoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuario Estándar',
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
    /**
     * Escenario: se consultan los valores respaldados de los Enums de PHP 8.2 del paquete.
     * Expectativa: cada caso coincide exactamente con las cadenas esperadas por el frontend.
     */
    #[Test]
    public function it_verifies_all_enums_have_correct_backed_string_values(): void
    {
        // given / when / then
        $this->assertSame('text', ColumnType::TEXT->value);
        $this->assertSame('avatar', ColumnType::AVATAR->value);
        $this->assertSame('badge', ColumnType::BADGE->value);
        $this->assertSame('currency', ColumnType::CURRENCY->value);
        $this->assertSame('date', ColumnType::DATE->value);
        $this->assertSame('datetime', ColumnType::DATETIME->value);
        $this->assertSame('boolean', ColumnType::BOOLEAN->value);
        $this->assertSame('link', ColumnType::LINK->value);
        $this->assertSame('json', ColumnType::JSON->value);

        $this->assertSame('text', FieldType::TEXT->value);
        $this->assertSame('email', FieldType::EMAIL->value);
        $this->assertSame('password', FieldType::PASSWORD->value);
        $this->assertSame('number', FieldType::NUMBER->value);
        $this->assertSame('textarea', FieldType::TEXTAREA->value);
        $this->assertSame('select', FieldType::SELECT->value);
        $this->assertSame('radio', FieldType::RADIO->value);
        $this->assertSame('checkbox', FieldType::CHECKBOX->value);
        $this->assertSame('switch', FieldType::SWITCH->value);
        $this->assertSame('file', FieldType::FILE->value);
        $this->assertSame('image', FieldType::IMAGE->value);
        $this->assertSame('hidden', FieldType::HIDDEN->value);

        $this->assertSame('start', Alignment::START->value);
        $this->assertSame('center', Alignment::CENTER->value);
        $this->assertSame('end', Alignment::END->value);

        $this->assertSame('both', PaginationPosition::BOTH->value);
        $this->assertSame('top', PaginationPosition::TOP->value);
        $this->assertSame('bottom', PaginationPosition::BOTTOM->value);
        $this->assertSame('none', PaginationPosition::NONE->value);

        $this->assertSame('toolbar', TabsPosition::TOOLBAR->value);
        $this->assertSame('top', TabsPosition::TOP->value);
    }

    /**
     * Escenario: se instancia un componente utilizando Makeable y se configuran identificadores y títulos.
     * Expectativa: los getters retornan los valores asignados y label toma title como respaldo.
     */
    #[Test]
    public function it_configures_identifiers_titles_and_labels_fluently(): void
    {
        // given
        $component = DummyComponent::make();

        // when
        $component
            ->id('user-component')
            ->title('User Title')
            ->subtitle('User Subtitle')
            ->description('User Description');

        // then
        $this->assertSame('user-component', $component->getId());
        $this->assertSame('User Title', $component->getTitle());
        $this->assertSame('User Title', $component->getLabel());
        $this->assertSame('User Subtitle', $component->getSubtitle());
        $this->assertSame('User Description', $component->getDescription());
    }

    /**
     * Escenario: se evalúa la visibilidad condicional con when y unless en backend, y visibleWhen para frontend.
     * Expectativa: los callbacks se ejecutan según la condición y se serializa la regla reactiva visibleWhen.
     */
    #[Test]
    public function it_controls_visibility_conditions_both_on_backend_and_frontend(): void
    {
        // given
        $component = DummyComponent::make();

        // when / then - visibilidad backend
        $this->assertTrue($component->isVisible());
        $component->hidden();
        $this->assertFalse($component->isVisible());
        $component->visible();
        $this->assertTrue($component->isVisible());

        // when - callbacks when / unless
        $component->when(true, function (DummyComponent $c) {
            $c->title('Visible via when');
        });
        $component->unless(true, function (DummyComponent $c) {
            $c->title('This should NOT be executed');
        });

        // then
        $this->assertSame('Visible via when', $component->getTitle());

        // when - visibilidad reactiva frontend
        $component->visibleWhen('status', 'active', '!==');

        // then
        $this->assertSame([
            'field' => 'status',
            'is' => 'active',
            'operator' => '!==',
        ], $component->getVisibleWhen());
    }

    /**
     * Escenario: se asignan permisos de autorización RBAC a un componente.
     * Expectativa: se almacenan los permisos y se puede consultar el primero o si posee permisos.
     */
    #[Test]
    public function it_manages_rbac_permissions_correctly(): void
    {
        // given
        $component = DummyComponent::make();

        // when
        $component->permission(['users.create', 'users.update']);

        // then
        $this->assertTrue($component->hasPermissions());
        $this->assertSame(['users.create', 'users.update'], $component->getPermissions());
        $this->assertSame('users.create', $component->getFirstPermission());

        // when - alias can()
        $single = DummyComponent::make()->can('users.delete');

        // then
        $this->assertTrue($single->hasPermissions());
        $this->assertSame(['users.delete'], $single->getPermissions());
    }

    /**
     * Escenario: se configuran opciones clave-valor desde array o auto-extraídas desde un Enum de PHP 8.1+.
     * Expectativa: se formatean como array normalizado [{value, label}].
     */
    #[Test]
    public function it_normalizes_options_from_array_and_php_enums(): void
    {
        // given
        $fromArray = DummyComponent::make();
        $fromEnum = DummyComponent::make();

        // when
        $fromArray->options([
            'admin' => 'Administrador',
            'editor' => 'Editor',
        ]);
        $fromEnum->optionsFromEnum(TestDummyRoleEnum::class);

        // then
        $this->assertSame([
            ['value' => 'admin', 'label' => 'Administrador'],
            ['value' => 'editor', 'label' => 'Editor'],
        ], $fromArray->getOptions());

        $this->assertSame([
            ['value' => 'admin', 'label' => 'Administrador'],
            ['value' => 'user', 'label' => 'Usuario Estándar'],
        ], $fromEnum->getOptions());
    }

    /**
     * Escenario: se intenta extraer opciones de una clase inexistente o que no es un Enum.
     * Expectativa: lanza InvalidArgumentException con un mensaje descriptivo en español.
     */
    #[Test]
    public function it_throws_exception_when_extracting_options_from_invalid_enum(): void
    {
        // given
        $component = DummyComponent::make();

        // then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La clase stdClass no es un Enum válido.');

        // when
        /** @phpstan-ignore argument.type */
        $component->optionsFromEnum(\stdClass::class);
    }

    /**
     * Escenario: se definen reglas de validación en modo creación (POST) y se extraen en modo actualización (PATCH).
     * Expectativa: en modo PATCH, la regla 'required' se convierte automáticamente en 'sometimes|required' (dirty tracking).
     */
    #[Test]
    public function it_adapts_validation_rules_for_patch_dirty_tracking(): void
    {
        // given
        $component = DummyComponent::make()
            ->required()
            ->string()
            ->min(5)
            ->max(100)
            ->unique('users', 'email', except: '10', idColumn: 'uuid');

        // when - creación (POST)
        $createRules = $component->getValidationRules(isUpdate: false);

        // then
        $this->assertContains('required', $createRules);
        $this->assertContains('string', $createRules);
        $this->assertContains('min:5', $createRules);
        $this->assertContains('max:100', $createRules);
        $this->assertContains('unique:users,email,10,uuid', $createRules);

        // when - actualización parcial (PATCH)
        $patchRules = $component->getValidationRules(isUpdate: true);

        // then
        $this->assertSame(['sometimes', 'required', 'string', 'min:5', 'max:100', 'unique:users,email,10,uuid'], $patchRules);
    }
}
