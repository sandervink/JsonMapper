<?php

declare(strict_types=1);

namespace JsonMapper\Tests\Unit\Handler;

use JsonMapper\Builders\PropertyBuilder;
use JsonMapper\Enums\Visibility;
use JsonMapper\Handler\PropertyMapper;
use JsonMapper\JsonMapperInterface;
use JsonMapper\Tests\Implementation\ComplexObject;
use JsonMapper\Tests\Implementation\SimpleObject;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;

class PropertyMapperTest extends TestCase
{
    /**
     * @covers \JsonMapper\Handler\PropertyMapper
     */
    public function testAdditionalJsonIsIgnored(): void
    {
        $propertyMapper = new PropertyMapper();
        $json = (object) ['file' => __FILE__];
        $object = new \stdClass();
        $wrapped = new ObjectWrapper($object);

        $propertyMapper->__invoke($json, $wrapped, new PropertyMap(), $this->createMock(JsonMapperInterface::class));

        self::assertEquals(new \stdClass(), $object);
    }

    /**
     * @covers \JsonMapper\Handler\PropertyMapper
     */
    public function testPublicScalarValueIsSet(): void
    {
        $fileProperty = PropertyBuilder::new()
            ->setName('file')
            ->setType('string')
            ->setIsNullable(false)
            ->setVisibility(Visibility::PUBLIC())
            ->build();
        $propertyMap = new PropertyMap();
        $propertyMap->addProperty($fileProperty);
        $json = (object) ['file' => __FILE__];
        $object = new \stdClass();
        $wrapped = new ObjectWrapper($object);
        $propertyMapper = new PropertyMapper();

        $propertyMapper->__invoke($json, $wrapped, $propertyMap, $this->createMock(JsonMapperInterface::class));

        self::assertEquals(__FILE__, $object->file);
    }

    /**
     * @covers \JsonMapper\Handler\PropertyMapper
     */
    public function testPublicBuiltinClassIsSet(): void
    {
        $property = PropertyBuilder::new()
            ->setName('createdAt')
            ->setType(\DateTimeImmutable::class)
            ->setIsNullable(false)
            ->setVisibility(Visibility::PUBLIC())
            ->build();
        $now = new \DateTimeImmutable();
        $propertyMap = new PropertyMap();
        $propertyMap->addProperty($property);
        $json = (object) ['createdAt' => $now->format('Y-m-d\TH:i:s.uP')];
        $object = new \stdClass();
        $wrapped = new ObjectWrapper($object);
        $propertyMapper = new PropertyMapper();

        $propertyMapper->__invoke($json, $wrapped, $propertyMap, $this->createMock(JsonMapperInterface::class));

        self::assertEquals($now, $object->createdAt);
    }

    /**
     * @covers \JsonMapper\Handler\PropertyMapper
     */
    public function testPublicCustomClassIsSet(): void
    {
        $property = PropertyBuilder::new()
            ->setName('child')
            ->setType(SimpleObject::class)
            ->setIsNullable(false)
            ->setVisibility(Visibility::PRIVATE())
            ->build();
        $propertyMap = new PropertyMap();
        $propertyMap->addProperty($property);
        $jsonMapper = $this->createMock(JsonMapperInterface::class);
        $jsonMapper->expects($this->once())
            ->method('mapObject')
            ->with((object) ['name' => __FUNCTION__], self::isInstanceOf(SimpleObject::class))
            ->willReturnCallback(static function (\stdClass $json, SimpleObject $object) {
                $object->setName($json->name);
            });
        $json = (object) ['child' => (object) ['name' => __FUNCTION__]];
        $object = new ComplexObject();
        $wrapped = new ObjectWrapper($object);
        $propertyMapper = new PropertyMapper();

        $propertyMapper->__invoke($json, $wrapped, $propertyMap, $jsonMapper);

        self::assertEquals(__FUNCTION__, $object->getChild()->getName());
    }
}
