<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form;

use Illuminate\Support\Traits\Macroable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasValidationRules;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\FieldContract;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Fields\CheckboxField;
use Warrior\SchemaBuilder\Form\Fields\DateField;
use Warrior\SchemaBuilder\Form\Fields\DateRangeField;
use Warrior\SchemaBuilder\Form\Fields\DateTimeField;
use Warrior\SchemaBuilder\Form\Fields\EmailField;
use Warrior\SchemaBuilder\Form\Fields\FileField;
use Warrior\SchemaBuilder\Form\Fields\HiddenField;
use Warrior\SchemaBuilder\Form\Fields\ImageField;
use Warrior\SchemaBuilder\Form\Fields\NumberField;
use Warrior\SchemaBuilder\Form\Fields\PasswordField;
use Warrior\SchemaBuilder\Form\Fields\RadioField;
use Warrior\SchemaBuilder\Form\Fields\SelectField;
use Warrior\SchemaBuilder\Form\Fields\SwitchField;
use Warrior\SchemaBuilder\Form\Fields\TextareaField;
use Warrior\SchemaBuilder\Form\Fields\TextField;

/** @phpstan-consistent-constructor */
class Field implements FieldContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasValidationRules;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected string $name;

    protected FieldType $type = FieldType::TEXT;

    protected ?string $placeholder = null;

    protected mixed $defaultValue = null;

    protected int $cols = 12;

    protected ?int $sm = null;

    protected ?int $md = null;

    protected ?int $lg = null;

    protected ?int $xl = null;

    protected bool $disabled = false;

    protected bool $readOnly = false;

    protected ?string $hint = null;

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    /**
     * @var array<string, mixed>
     */
    protected array $extraAttributes = [];

    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucwords(str_replace(['_', '-'], ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function type(FieldType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function defaultValue(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function cols(int $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function sm(int $cols): static
    {
        $this->sm = $cols;

        return $this;
    }

    public function md(int $cols): static
    {
        $this->md = $cols;

        return $this;
    }

    public function lg(int $cols): static
    {
        $this->lg = $cols;

        return $this;
    }

    public function xl(int $cols): static
    {
        $this->xl = $cols;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function readOnly(bool $readOnly = true): static
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function hint(string $hint): static
    {
        $this->hint = $hint;

        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function extra(string $key, mixed $value): static
    {
        $this->extraAttributes[$key] = $value;

        return $this;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function toValidationRules(bool $isUpdate = false): array
    {
        $rules = $this->getValidationRules($isUpdate);

        return ! empty($rules) ? [$this->getName() => $rules] : [];
    }

    // Factory methods for specialized fields
    public static function text(string $name, ?string $label = null): TextField
    {
        return new TextField($name, $label);
    }

    public static function email(string $name, ?string $label = null): EmailField
    {
        return new EmailField($name, $label);
    }

    public static function password(string $name, ?string $label = null): PasswordField
    {
        return new PasswordField($name, $label);
    }

    public static function number(string $name, ?string $label = null): NumberField
    {
        return new NumberField($name, $label);
    }

    public static function textarea(string $name, ?string $label = null): TextareaField
    {
        return new TextareaField($name, $label);
    }

    public static function select(string $name, ?string $label = null): SelectField
    {
        return new SelectField($name, $label);
    }

    public static function radio(string $name, ?string $label = null): RadioField
    {
        return new RadioField($name, $label);
    }

    public static function checkbox(string $name, ?string $label = null): CheckboxField
    {
        return new CheckboxField($name, $label);
    }

    public static function switch(string $name, ?string $label = null): SwitchField
    {
        return new SwitchField($name, $label);
    }

    public static function date(string $name, ?string $label = null): DateField
    {
        return new DateField($name, $label);
    }

    public static function datetime(string $name, ?string $label = null): DateTimeField
    {
        return new DateTimeField($name, $label);
    }

    public static function dateRange(string $name, ?string $label = null): DateRangeField
    {
        return new DateRangeField($name, $label);
    }

    public static function file(string $name, ?string $label = null): FileField
    {
        return new FileField($name, $label);
    }

    public static function image(string $name, ?string $label = null): ImageField
    {
        return new ImageField($name, $label);
    }

    public static function hidden(string $name, mixed $value = null): HiddenField
    {
        $field = new HiddenField($name);
        if ($value !== null) {
            $field->value($value);
        }

        return $field;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'fieldName' => $this->name,
            'label' => $this->getLabel(),
            'type' => $this->type->value,
            'placeholder' => $this->placeholder,
            'defaultValue' => $this->defaultValue,
            'rules' => $this->getValidationRules(isUpdate: false),
            'cols' => $this->cols,
            'sm' => $this->sm,
            'md' => $this->md,
            'lg' => $this->lg,
            'xl' => $this->xl,
            'disabled' => $this->disabled,
            'readOnly' => $this->readOnly,
            'hint' => $this->hint,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'visibleWhen' => $this->visibleWhen,
            'permissions' => $this->permissions,
        ];

        return array_merge($data, $this->extraAttributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
