<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

/**
 * Trait HasIdAndTitle
 *
 * Proporciona métodos fluidos para gestionar identificadores, títulos, etiquetas,
 * subtítulos y descripciones en componentes visuales y esquemas.
 */
trait HasIdAndTitle
{
    /**
     * Identificador único del componente o esquema.
     */
    protected ?string $id = null;

    /**
     * Título principal visible.
     */
    protected ?string $title = null;

    /**
     * Etiqueta para campos o inputs de formulario.
     */
    protected ?string $label = null;

    /**
     * Subtítulo descriptivo secundario.
     */
    protected ?string $subtitle = null;

    /**
     * Descripción detallada del componente o formulario.
     */
    protected ?string $description = null;

    /**
     * Asigna el identificador único del elemento.
     */
    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Obtiene el identificador único o una cadena vacía si no fue configurado.
     */
    public function getId(): string
    {
        return $this->id ?? '';
    }

    /**
     * Asigna el título visible del elemento.
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Obtiene el título configurado o null si no existe.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Asigna una etiqueta legible para el campo o input.
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Obtiene la etiqueta visible. Si no está configurada, utiliza el título como respaldo.
     */
    public function getLabel(): string
    {
        return $this->label ?? $this->title ?? '';
    }

    /**
     * Asigna el subtítulo del esquema o componente.
     */
    public function subtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Obtiene el subtítulo secundario o null si no fue asignado.
     */
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * Asigna una descripción extensa o texto explicativo.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Obtiene la descripción detallada.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
