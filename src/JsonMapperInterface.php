<?php

declare(strict_types=1);

namespace DannyVanDerSluijs\JsonMapper;

interface JsonMapperInterface
{
    public function mapObject(\stdClass $json, object $object): void;
}
