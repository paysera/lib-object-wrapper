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
    public function testOffsetExists()
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $this->assertNotEmpty($object['a']);
        $this->assertNotEmpty($object['c']);
        $this->assertEmpty($object['d']);
    }

    public function testOffsetGet()
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => (object)['d' => 'e']]);
        $this->assertSame('b', $object['a']);
        $this->assertNull($object['d']);
        $this->assertDeepEquals((object)['d' => 'e'], $object['c']);
        $this->assertInstanceOf(ObjectWrapper::class, $object['c']);
    }

    public function testOffsetSet()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Modifying ObjectWrapper is not allowed');
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $object['q'] = 'e';
    }

    public function testOffsetUnset()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Modifying ObjectWrapper is not allowed');
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        unset($object['q']);
    }

    public function testGetIterator()
    {
        $object = new ObjectWrapper((object)['a' => 'b', 'c' => ['d' => 'e']]);
        $this->assertSame(['a' => 'b', 'c' => ['d' => 'e']], iterator_to_array($object));
    }

    public function testGetRequired()
    {
        $object = new ObjectWrapper((object)['a' => 'b']);
        $this->assertSame('b', $object->getRequired('a'));
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "c"');
        $object->getRequired('c');
    }

    public function testGetRequiredBoolWithNoItem()
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "a"');
        $object->getRequiredBool('a');
    }

    public function testGetRequiredFloatWithNoItem()
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "a"');
        $object->getRequiredFloat('a');
    }

    public function testGetRequiredIntWithNoItem()
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "a"');
        $object->getRequiredInt('a');
    }

    public function testGetRequiredObjectWithNoItem()
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "a"');
        $object->getRequiredObject('a');
    }

    public function testGetRequiredStringWithNoItem()
    {
        $object = new ObjectWrapper((object)[]);
        $this->expectException(MissingItemException::class);
        $this->expectExceptionMessage('Missing required key "a"');
        $object->getRequiredString('a');
    }

    public function testGetRequiredBool()
    {
        $object = new ObjectWrapper((object)['a' => true, 'b' => 'other type']);
        $this->assertTrue($object->getRequiredBool('a'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected boolean but fot string for key "b"');
        $object->getRequiredBool('b');
    }

    public function testGetRequiredFloat()
    {
        $object = new ObjectWrapper((object)['a' => 1.23, 'b' => 1, 'c' => 'other type']);
        $this->assertSame(1.23, $object->getRequiredFloat('a'));
        $this->assertSame((float)1, $object->getRequiredFloat('b'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected float but fot string for key "c"');
        $object->getRequiredFloat('c');
    }

    public function testGetRequiredInt()
    {
        $object = new ObjectWrapper((object)['a' => 1, 'b' => 1.23]);
        $this->assertSame(1, $object->getRequiredInt('a'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected integer but fot float for key "b"');
        $object->getRequiredInt('b');
    }

    public function testGetRequiredObject()
    {
        $data = new stdClass();
        $data->a = 'a';
        $object = new ObjectWrapper((object)['a' => $data, 'b' => 'other type']);
        $this->assertDeepEquals($data, $object->getRequiredObject('a'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected object but fot string for key "b"');
        $object->getRequiredObject('b');
    }

    public function testGetRequiredString()
    {
        $object = new ObjectWrapper((object)['a' => 'string', 'b' => 123]);
        $this->assertSame('string', $object->getRequiredString('a'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected string but fot integer for key "b"');
        $object->getRequiredString('b');
    }

    public function testGetBool()
    {
        $object = new ObjectWrapper((object)['a' => true, 'b' => 'other type']);
        $this->assertTrue($object->getBool('a'));
        $this->assertNull($object->getBool('c'));
        $this->assertTrue($object->getBool('c', true));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected boolean but fot string for key "b"');
        $object->getRequiredBool('b');
    }

    public function testGetFloat()
    {
        $object = new ObjectWrapper((object)['a' => 1.23, 'b' => 1, 'c' => 'other type']);
        $this->assertSame(1.23, $object->getFloat('a'));
        $this->assertNull($object->getFloat('d'));
        $this->assertSame(2.34, $object->getFloat('d', 2.34));
        $this->assertSame((float)1, $object->getFloat('b'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected float but fot string for key "c"');
        $object->getFloat('c');
    }

    public function testGetInt()
    {
        $object = new ObjectWrapper((object)['a' => 1, 'b' => 1.23]);
        $this->assertSame(1, $object->getInt('a'));
        $this->assertNull($object->getInt('c'));
        $this->assertSame(2, $object->getInt('c', 2));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected integer but fot float for key "b"');
        $object->getInt('b');
    }

    public function testGetObject()
    {
        $data = new stdClass();
        $data->a = 'a';
        $object = new ObjectWrapper((object)['a' => $data, 'b' => 'other type']);
        $this->assertDeepEquals($data, $object->getObject('a'));
        $this->assertNull($object->getObject('c'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected object but fot string for key "b"');
        $object->getObject('b');
    }

    public function testGetString()
    {
        $object = new ObjectWrapper((object)['a' => 'string', 'b' => 123]);
        $this->assertSame('string', $object->getString('a'));
        $this->assertNull($object->getString('c'));
        $this->assertSame('default', $object->getString('c', 'default'));
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected string but fot integer for key "b"');
        $object->getString('b');
    }

    public function testGetArray()
    {
        $array = [1, '2', 3.0, false, (object)['a' => 'b']];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertDeepEquals($array, $object->getArray('a'));
        $this->assertSame([], $object->getArray('non_existent_key'));
        $this->assertSame([1, 2, 3], $object->getArray('non_existent_key', [1, 2, 3]));
        $this->assertSame([], $object->getArray('empty', [1, 2, 3]));
    }

    public function testGetArrayWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot string for key "a"');
        $object->getArray('a');
    }

    public function testGetArrayWithNull()
    {
        $object = new ObjectWrapper((object)['a' => null]);
        $this->assertSame([], $object->getArray('a'));
    }

    public function testGetArrayOfBool()
    {
        $array = [false, false, true];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfBool('a'));
        $this->assertSame([], $object->getArrayOfBool('non_existent_key'));
        $this->assertSame([], $object->getArrayOfBool('empty'));
    }

    public function testGetArrayOfBoolWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot string for key "a"');
        $object->getArrayOfBool('a');
    }

    public function testGetArrayOfBoolWithDifferentItemType()
    {
        $array = [false, false, 0, true];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected boolean but fot integer for key "a"');
        $object->getArrayOfBool('a');
    }

    public function testGetArrayOfBoolWithNullItem()
    {
        $array = [false, false, null, true];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected boolean but fot NULL for key "a"');
        $object->getArrayOfBool('a');
    }

    public function testGetArrayOfFloat()
    {
        $array = [1, 1.0, 2.3];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame([1.0, 1.0, 2.3], $object->getArrayOfFloat('a'));
        $this->assertSame([], $object->getArrayOfFloat('non_existent_key'));
        $this->assertSame([], $object->getArrayOfFloat('empty'));
    }

    public function testGetArrayOfFloatWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot string for key "a"');
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfFloatWithDifferentItemType()
    {
        $array = [1.0, 2.3, false];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected float but fot boolean for key "a"');
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfFloatWithNullItem()
    {
        $array = [1.0, 2.0, null, 3.3];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected float but fot NULL for key "a"');
        $object->getArrayOfFloat('a');
    }

    public function testGetArrayOfInt()
    {
        $array = [0, -10, 2211223];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfInt('a'));
        $this->assertSame([], $object->getArrayOfInt('non_existent_key'));
        $this->assertSame([], $object->getArrayOfInt('empty'));
    }

    public function testGetArrayOfIntWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot string for key "a"');
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfIntWithDifferentItemType()
    {
        $array = [1, 9, 2.1, 4];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected integer but fot float for key "a"');
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfIntWithNullItem()
    {
        $array = [1, 3, null, 5];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected integer but fot NULL for key "a"');
        $object->getArrayOfInt('a');
    }

    public function testGetArrayOfString()
    {
        $array = ['', '123123', 'string'];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertSame($array, $object->getArrayOfString('a'));
        $this->assertSame([], $object->getArrayOfString('non_existent_key'));
        $this->assertSame([], $object->getArrayOfString('empty'));
    }

    public function testGetArrayOfStringWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 1]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot integer for key "a"');
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfStringWithDifferentItemType()
    {
        $array = ['string', 'aaa', 4];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected string but fot integer for key "a"');
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfStringWithNullItem()
    {
        $array = ['string', 'item', null];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected string but fot NULL for key "a"');
        $object->getArrayOfString('a');
    }

    public function testGetArrayOfObject()
    {
        $structure = new stdClass();
        $jsonStructure = json_decode('{"a":"b"}');
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], $structure, $jsonStructure];
        $object = new ObjectWrapper((object)['a' => $array, 'empty' => []]);
        $this->assertDeepEquals($array, $object->getArrayOfObject('a'));
        $this->assertSame([], $object->getArrayOfObject('non_existent_key'));
        $this->assertSame([], $object->getArrayOfObject('empty'));
    }

    public function testGetArrayOfObjectWithDifferentType()
    {
        $object = new ObjectWrapper((object)['a' => 1]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot integer for key "a"');
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithDifferentItemType()
    {
        $object = new ObjectWrapper((object)['a' => 'string']);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected array but fot string for key "a"');
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithNullItem()
    {
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], null];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected object but fot NULL for key "a"');
        $object->getArrayOfObject('a');
    }

    public function testGetArrayOfObjectWithAssociativeArrayItem()
    {
        $array = [(object)['a' => 'b'], (object)[0 => 0, 1 => 1], ['a' => 'b']];
        $object = new ObjectWrapper((object)['a' => $array]);
        $this->expectException(InvalidItemTypeException::class);
        $this->expectExceptionMessage('Expected object but fot array for key "a"');
        $object->getArrayOfObject('a');
    }

    public function testDoesNotAffectInput()
    {
        $innerData = (object)['b' => 'c'];
        $data = new stdClass();
        $data->a = $innerData;
        $object = new ObjectWrapper($data);
        $object->getRequiredObject('a')->getRequiredString('b');
        $this->assertSame('c', $innerData->b);
        $this->assertContains('c', (array) $data->a);
    }

    private function assertDeepEquals($expectedData, $dataWithWrappers)
    {
        $this->assertEquals($expectedData, $this->unwrap($dataWithWrappers));
    }

    private function unwrap($dataWithWrappers)
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
