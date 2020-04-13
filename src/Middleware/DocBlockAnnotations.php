<?php

declare(strict_types=1);

namespace JsonMapper\Middleware;

use JsonMapper\Builders\PropertyBuilder;
use JsonMapper\Enums\Visibility;
use JsonMapper\Helpers\AnnotationHelper;
use JsonMapper\JsonMapperInterface;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;

class DocBlockAnnotations extends AbstractMiddleware
{
    public function handle(\stdClass $json, ObjectWrapper $object, PropertyMap $map, JsonMapperInterface $mapper): void
    {
        $properties = $object->getReflectedObject()->getProperties();

        foreach ($properties as $property) {
            $name = $property->getName();
            $docblock = $property->getDocComment();

            if ($docblock === false) {
                continue;
            }

            $annotations = AnnotationHelper::parseAnnotations($docblock);
            $type = $annotations['var'][0];

            $property = PropertyBuilder::new()
                ->setName($name)
                ->setType($type)
                ->setIsNullable(AnnotationHelper::isNullable($annotations['var'][0]))
                ->setVisibility(Visibility::fromReflectionProperty($property))
                ->build();
            $map->addProperty($property);
        }
    }
}