<?php

declare(strict_types=1);

namespace DannyVanDerSluijs\JsonMapper\ValueObjects;

use DannyVanDerSluijs\JsonMapper\Enums\Visibility;

class Property
{
    /** @var string */
    private $name;
    /** @var string */
    private $type;
    /** @var bool */
    private $isNullable;
    /** @var Visibility */
    private $visibility;

    public function __construct(string $name, string $type, bool $isNullable, Visibility $visibility)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isNullable = $isNullable;
        $this->visibility = $visibility;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function getVisibility(): Visibility
    {
        return $this->visibility;
    }
}
