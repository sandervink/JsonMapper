<?php

declare(strict_types=1);

namespace JsonMapper\Handler;

use JsonMapper\Enums\Visibility;
use JsonMapper\Helpers\TypeHelper;
use JsonMapper\JsonMapperInterface;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;

class PropertyMapper
{
    public function __invoke(
        \stdClass $json,
        ObjectWrapper $object,
        PropertyMap $propertyMap,
        JsonMapperInterface $mapper
    ): void {
        $values = (array) $json;
        foreach ($values as $key => $value) {
            if (! $propertyMap->hasProperty($key)) {
                continue;
            }

            $propertyInfo = $propertyMap->getProperty($key);
            $type = $propertyInfo->getType();

            if (TypeHelper::isBuiltinClass($type)) {
                $value = new $type($value);
            }
            if (TypeHelper::isScalarType($type)) {
                $value = TypeHelper::cast($value, $type);
            }
            if (TypeHelper::isCustomClass($type)) {
                $instance = new $type();
                $mapper->mapObject($value, $instance);
                $value = $instance;
            }

            if ($propertyInfo->getVisibility()->equals(Visibility::PUBLIC())) {
                $object->getObject()->$key = $value;
                continue;
            }

            $setterMethod = 'set' . ucfirst($key);
            if (method_exists($object->getObject(), $setterMethod)) {
                $object->getObject()->$setterMethod($value);
            }
        }
    }
}
