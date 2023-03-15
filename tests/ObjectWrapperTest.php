<?php

declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Tests;

use stdClass;
use RuntimeException;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemTypeException;
use Paysera\Component\ObjectWrapper\Exception\MissingItemException;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;

class ObjectWrapperTest extends TestCase
{
    public function testOffsetExists(): void
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $this->assertTrue(isset($object['a']));
        $this->assertTrue(isset($object['c']));
        $this->assertFalse(isset($object['d']));
    }

    public function testOffsetGet(): void
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => (object)['d' => 'e']]);
        $this->assertSame('b', $object['a']);
        $this->assertNull($object['d']);
        $this->assertDeepEquals((object)['d' => 'e'], $object['c']);
        $this->assertInstanceOf(ObjectWrapper::class, $object['c']);
    }

    public function testOffsetSet(): void
    {
        $this->expectException(RuntimeException::class);
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $object['q'] = 'e';
    }

    public function testOffsetUnset(): void
    {
        $this->expectException(RuntimeException::class);
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        unset($object['q']);
    }

    public function testGetIterator(): void
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $this->assertSame(['a' => 'b', 'c' => ['d' => 'e']], iterator_to_array($object));
    }

    public function testGetRequired(): void
    {
        $object = new ObjectWrapper((object)['a' => 'b']);
        $this->assertSame('b', $object->getRequired('a'));
        $this->expectException(MissingItemException::class);
        $object->getRequired('c');
    }

    public function testGetRequiredBoolWithNoItem(): void
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $object->getRequiredBool('a');
    }

    public function testGetRequiredFloatWithNoItem(): void
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $object->getRequiredFloat('a');
    }

    public function testGetRequiredIntWithNoItem(): void
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $object->getRequiredInt('a');
    }

    public function testGetRequiredObjectWithNoItem(): void
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $object->getRequiredObject('a');
    }

    public function testGetRequiredStringWithNoItem(): void
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $object->getRequiredString('a');
    }

    public function testGetRequiredBool(): void
    {
        $object = new ObjectWrapper((object)['a' => true, 'b' => 'other type', 'c' => 'false', 'd' => '1', 'e' => 0]);
        $this->assertTrue($object->getRequiredBool('a'));
        $this->assertFalse($object->getRequiredBool('c'));
        $this->assertTrue($object->getRequiredBool('d'));
        $this->assertFalse($object->getRequiredBool('e'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredBool('b');
    }

    public function testGetRequiredFloat(): void
    {
        $object = new ObjectWrapper((object)['a' => 1.23, 'b' => 1, 'c' => 'other type']);
        $this->assertSame(1.23, $object->getRequiredFloat('a'));
        $this->assertSame(1.0, $object->getRequiredFloat('b'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredFloat('c');
    }

    public function testGetRequiredInt(): void
    {
        $object = new ObjectWrapper((object)['a' => 1, 'b' => 1.23]);
        $this->assertSame(1, $object->getRequiredInt('a'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredInt('b');
    }

    public function testGetRequiredObject(): void
    {
        $data = new stdClass();
        $data->a = 'a';
        $object = new ObjectWrapper((object)['a' => $data, 'b' => 'other type']);
        $this->assertDeepEquals($data, $object->getRequiredObject('a'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredObject('b');
    }

    public function testGetRequiredString(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string', 'b' => 123]);
        $this->assertSame('string', $object->getRequiredString('a'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredString('b');
    }

    public function testGetBool(): void
    {
        $object = new ObjectWrapper((object)['a' => true, 'b' => 'other type']);
        $this->assertTrue($object->getBool('a'));
        $this->assertNull($object->getBool('c'));
        $this->assertTrue($object->getBool('c', true));
        $this->expectException(InvalidItemTypeException::class);
        $object->getRequiredBool('b');
    }

    public function testGetFloat(): void
    {
        $object = new ObjectWrapper((object)['a' => 1.23, 'b' => 1, 'c' => 'other type']);
        $this->assertSame(1.23, $object->getFloat('a'));
        $this->assertNull($object->getFloat('d'));
        $this->assertSame(2.34, $object->getFloat('d', 2.34));
        $this->assertSame((float)1, $object->getFloat('b'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getFloat('c');
    }

    public function testGetInt(): void
    {
        $object = new ObjectWrapper((object)['a' => 1, 'b' => 1.23]);
        $this->assertSame(1, $object->getInt('a'));
        $this->assertNull($object->getInt('c'));
        $this->assertSame(2, $object->getInt('c', 2));
        $this->expectException(InvalidItemTypeException::class);
        $object->getInt('b');
    }

    public function testGetObject(): void
    {
        $data = new stdClass();
        $data->a = 'a';
        $object = new ObjectWrapper((object)['a' => $data, 'b' => 'other type']);
        $this->assertDeepEquals($data, $object->getObject('a'));
        $this->assertNull($object->getObject('c'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getObject('b');
    }

    public function testGetString(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string', 'b' => 123]);
        $this->assertSame('string', $object->getString('a'));
        $this->assertNull($object->getString('c'));
        $this->assertSame('default', $object->getString('c', 'default'));
        $this->expectException(InvalidItemTypeException::class);
        $object->getString('b');
    }

    public function testGetArray(): void
    {
        $array = [1, '2', 3.0, false, (object)['a' => 'b']];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertDeepEquals($array, $object->getArray('a'));
        $this->assertSame([], $object->getArray('non_existent_key'));
        $this->assertSame([1, 2, 3], $object->getArray('non_existent_key', [1, 2, 3]));
        $this->assertSame([], $object->getArray('empty', [1, 2, 3]));
    }

    public function testGetArrayWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArray('a');
    }

    public function testGetArrayWithNull(): void
    {
        $object = new ObjectWrapper((object)['a' => null]);
        $this->assertSame([], $object->getArray('a'));
    }

    public function testGetArrayOfBool(): void
    {
        $array = [false, false, true];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfBool('a'));
        $this->assertSame([], $object->getArrayOfBool('non_existent_key'));
        $this->assertSame([], $object->getArrayOfBool('empty'));
    }

    public function testGetArrayOfBoolWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfBool('a');
    }

    public function testGetArrayOfBoolWithDifferentItemType(): void
    {
        $array = [false, 'false', 0, true, 1, 'true'];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->assertSame(
            [false, false, false, true, true, true],
            $object->getArrayOfBool('a')
        );
    }

    public function testGetArrayOfFloat(): void
    {
        $array = [1, 1.0, 2.3];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame([1.0, 1.0, 2.3], $object->getArrayOfFloat('a'));
        $this->assertSame([], $object->getArrayOfFloat('non_existent_key'));
        $this->assertSame([], $object->getArrayOfFloat('empty'));
    }

    public function testGetArrayOfFloatWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfFloatWithDifferentItemType(): void
    {
        $array = [1.0, 2.3, false];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfFloatWithNullItem(): void
    {
        $array = [1.0, 2.0, null, 3.3];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfInt(): void
    {
        $array = [0, -10, 2211223];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfInt('a'));
        $this->assertSame([], $object->getArrayOfInt('non_existent_key'));
        $this->assertSame([], $object->getArrayOfInt('empty'));
    }

    public function testGetArrayOfIntWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfIntWithDifferentItemType(): void
    {
        $array = [1, 9, 2.1, 4];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfIntWithNullItem(): void
    {
        $array = [1, 3, null, 5];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfString(): void
    {
        $array = ['', '123123', 'string'];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfString('a'));
        $this->assertSame([], $object->getArrayOfString('non_existent_key'));
        $this->assertSame([], $object->getArrayOfString('empty'));
    }

    public function testGetArrayOfStringWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 1]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfStringWithDifferentItemType(): void
    {
        $array = ['string', 'aaa', 4];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfStringWithNullItem(): void
    {
        $array = ['string', 'item', null];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfObject(): void
    {
        $structure = new stdClass();
        $jsonStructure = json_decode('{"a":"b"}');
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], $structure, $jsonStructure];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertDeepEquals($array, $object->getArrayOfObject('a'));
        $this->assertSame([], $object->getArrayOfObject('non_existent_key'));
        $this->assertSame([], $object->getArrayOfObject('empty'));
    }

    public function testGetArrayOfObjectWithDifferentType(): void
    {
        $object = new ObjectWrapper((object)['a' => 1]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithDifferentItemType(): void
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithNullItem(): void
    {
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], null];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithAssociativeArrayItem(): void
    {
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], ['a' => 'b']];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $object->getArrayOfObject('a');
    }

    public function testDoesNotAffectInput(): void
    {
        $innerData = (object)['b' => 'c'];
        $data = new stdClass();
        $data->a = $innerData;
        $object = new ObjectWrapper($data);
        $object->getRequiredObject('a')->getRequiredString('b');
        $this->assertSame($innerData, $data->a);
    }

    public function testGetOriginalData(): void
    {
        $innerData = (object)['b' => 'c'];
        $data = new stdClass();
        $data->a = $innerData;
        $object = new ObjectWrapper($data);
        $originalData = $object->getOriginalData();
        $this->assertDeepEquals($data, $originalData);
    }

    public function testGetOriginalDataAsArray(): void
    {
        $innerData = (object)['b' => 'c'];
        $data = new stdClass();
        $data->a = $innerData;

        $firstArrayItem = new stdClass();
        $firstArrayItem->a = 1;
        $secondArrayItem = new stdClass();
        $secondArrayItem->b = 2;
        $thirdArrayItem = new stdClass();
        $thirdArrayItem->c = 3;
        $data->b = [$firstArrayItem, $secondArrayItem, $thirdArrayItem, 4];

        $object = new ObjectWrapper($data);
        $originalData = $object->getDataAsArray();

        $expectedArray = [
            'a' => ['b' => 'c'],
            'b' => [
                ['a' => 1],
                ['b' => 2],
                ['c' => 3],
                4,
            ],
        ];
        $this->assertDeepEquals($expectedArray, $originalData);
    }

    private function assertDeepEquals($expectedData, $dataWithWrappers): void
    {
        $this->assertEquals($expectedData, $this->unwrap($dataWithWrappers));
    }

    private function unwrap($dataWithWrappers): mixed
    {
        if ($dataWithWrappers instanceof ObjectWrapper) {
            $result = new stdClass();
            foreach ($dataWithWrappers as $key => $value) {
                $result->$key = $this->unwrap($value);
            }

            return $result;
        }

        if (is_array($dataWithWrappers)) {
            return array_map([$this, 'unwrap'], $dataWithWrappers);
        }

        return $dataWithWrappers;
    }
}
