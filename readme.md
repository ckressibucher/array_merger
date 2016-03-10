ArrayMerger
============

This is a simple utility to recursively merge php arrays.
It's just one class, providing a static method. Alternatively, you can
create an instance, configure it, and then call an instance method.

[![Build Status](https://travis-ci.org/ckressibucher/array_merger.svg?branch=master)](https://travis-ci.org/ckressibucher/array_merger)

Compatibility
------------

This library currently works with php-5.5 until php-7.0.
If you need support for php-5.4, use version *1.0.0*.

Basic Usage
-----------

```php
// the default array has lower priority
$default = array(0 => 'a', 1 => 'b', 'x' => 'z');

// the values of the $precedence array have higher priority than
// the values of the $default array
$precedence = array(0 => 'a', 'x' => 'y', 'y' => 'y');

// use the static method...
$merged = \Ckr\Util\ArrayMerger::doMerge($default, $precedence);

// ... or create an object and call the instance method
$obj = new \Ckr\Util\ArrayMerger($default, $precedence);
$merged = $obj->mergeData();

print_r($merged);
/*
Array
(
    [0] => a // from $default
    [1] => b // from $default
    [x] => y // from $precedence (overwrite value from $default)
    [2] => a // from $precedence[0] (values associated to numeric keys get appended by default)
    [y] => y // from $precedence (new value, associated to string key)
)
*/
```

Another simple example with multidimensional input arrays:

```php
$default = array('outer' => array('a' => 'a', 'b' => 'b'));
$precedence = array('outer' => array('b' => 'x'));
$merged = \Ckr\Util\ArrayMerger::doMerge($default, $precedence);

print_r($merged);
/*
Array
(
    [outer] => Array
        (
            [a] => a // from $default
            [b] => x // from $precedence, overwriting $default['outer']['b']
        )

)
*/
```

Configuration
-------------

There are three flags available to determine the exact behaviour or the
merge operation:

```php
    /**
     * Given the default array has a scalar value 'v' at key 'k' and
     * the precedence array has a sub array at the same key 'k'.
     *
     * If flag is set, the default scalar value 'v' is wrapped in
     * an array [0 => 'v'] before the merge is done.
     *
     * If the flag is NOT set, then an exception is thrown.
     *
     * Default: not set
     */
    const FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION = 1;

    /**
     * If flag is set, the value of the precedence array for a given key
     * will overwrite the value of the default array for the same key.
     *
     * If flag is NOT set, the value of the precedence array is appended
     * to the default array. (or skipped, depending on
     * FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS)
     *
     * Default: not set
     */
    const FLAG_OVERWRITE_NUMERIC_KEY = 2;

    /**
     * It flag is set, a value of the precedence array for a numeric key is
     * only appended, if it does not exist yet in the default array.
     *
     * If flag is NOT set, the value of the precedence array is appended
     * to the default array (or overwrites the default array's value with the same key,
     * depending on FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION)
     *
     * This flag has the lower priority as FLAG_OVERWRITE_NUMERIC_KEY, i.e. if
     * FLAG_OVERWRITE_NUMERIC_KEY is set, then it doesn't matter what the value of
     * FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS is
     * (the value is added to the default array at the given index, regardless of other values
     *  in the array)
     *
     * Note that this flag may slow down the operation on very large (default) arrays.
     *
     * Default: not set
     */
    const FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS = 4;
```

Configuration values are applied by flags. You can combine them, before passing to the constructor or static method:

```php
    $flags = \Ckr\Util\ArrayMerger::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION
                 | \Ckr\Util\ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY;
    $merged = \Ckr\Util\ArrayMerger::doMerge($default, $precedence, $flags);
```

If you use an instance of the class, you can dynamically set and unset flags:

```php
$default = array(0 => 'a', 1 => 'b', 'x' => 'z');
$precedence = array(0 => 'a', 'x' => 'y', 'y' => 'y');

$obj = new \Ckr\Util\ArrayMerger($default, $precedence);
$merged = $obj->allowConversionFromScalarToArray(true)
   ->overwriteNumericKey(true)
   ->mergeData();

$merged2 = $obj->overwriteNumericKey(false)->mergeData();

echo count($merged);  // 4
echo count($merged2); // 5
```

For more examples, please see the phpSpec methods.

