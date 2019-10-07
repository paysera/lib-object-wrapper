# ObjectWrapper

This library provides class that lets easily get structured items from JSON-decoded data.

## Why?

So that it would be easier and quicker to:
- check correct data types. This allows to use PHP7 static type-hints easily;
- check required keys/items;
- give defaults to missing items;
- provide information about exact place in object tree where the data fails your requirements.

## Usage

```php
$jsonString = json_encode([
    // ...
]);
/** @var stdClass $jsonData */
$jsonData = json_decode($jsonString);

$data = new ObjectWrapper($jsonData);

// Use like an associative array if needed
foreach ($data as $key => $value) {
    var_dump(isset($data[$value]));
    var_dump($data[$value]);
}

var_dump($data['non_existing']);    // just null - no notice

var_dump($data->getRequired('required_key'));   // throws exception if missing

var_dump($data->getString('key_with_string'));  // throws exception if not string, defaults to null
var_dump($data->getString('key_with_string', 'default'));  // different default
var_dump($data->getRequiredString('key_with_string'));  // throws exception if missing or not string

var_dump(get_class($data->getRequiredObject('inner_data'))); // always another ObjectWrapper instance
var_dump(get_class($data->getObject('inner_data'))); // another ObjectWrapper instance or null

var_dump($data->getArrayOfString('keys'));    // array of strings
var_dump($data->getArrayOfOject('children')); // array of ObjectWrapper instances

try {
    $data->getRequiredObject('inner_data')->getArrayOfObject('list')[0]->getRequiredString('key');
} catch (MissingItemException $e) {
    echo $e->getMessage(); // Missing required key "inner_data.list.0.key"
    echo $e->getKey();     // inner_data.list.0.key
}
```

## Methods

- `$data[$key]` - use array accessor to get any value inside, returns `null` if it's unavailable. If value is object,
instance of `ObjectWrapper` will be returned;
- `getRequired`. Returns mixed value, but checks that it is provided;
- `getRequiredBool`. Returns `bool`;
- `getBool`. Returns `bool` or `null`;
- `getRequiredFloat`. Returns `float`. Accepts `int`, but casts it to `float`;
- `getFloat`. Returns `float` or `null`. Accepts `int`, but casts it to `float`;
- `getRequiredInt`. Returns `int`;
- `getInt`. Returns `int` or `null`;
- `getRequiredObject`. Returns another `ObjectWrapper` instance;
- `getObject`. Returns another `ObjectWrapper` instance or `null`;
- `getRequiredString`. Returns `string`;
- `getString`. Returns `string` or `null`;
- `getArray`. Returns `array`. If no item is provided, returns empty array, so no `getRequiredArray` is available;
- `getArrayOfBool`. Returns `array`, all it's items are `bool` (or it's empty);
- `getArrayOfFloat`. Returns `array`, all it's items are `float` (or it's empty);
- `getArrayOfInt`. Returns `array`, all it's items are `int` (or it's empty);
- `getArrayOfString`. Returns `array`, all it's items are `string` (or it's empty);
- `getArrayOfObject`. Returns `array`, all it's items are instances of `ObjectWrapper` (or it's empty).
- `getDataAsArray`. Returns `array`, all it's items are `array` or primitive types.
- `getOriginalData`. Returns `stdClass`, the original data passed to constructor.

## Some things to keep in mind

- `stdClass` is used for objects - this is default for `json_decode`. This is to easily check where the data was an
object and where it was an array. For example, if empty object or empty array is passed as JSON and you
denormalize to an array, there is no way to check what was the type of original data;
- `null` values are treated the same as it would not be provided at all:
  - it will not throw `InvalidItemTypeException` if you provide `null` and some type was expected (even array or object);
  - it will throw `MissingItemException` even if you provide the value but it is `null`;
- the object cannot be modified - setting or unsetting anything is not supported.
