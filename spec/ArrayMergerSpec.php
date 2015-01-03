<?php

namespace spec\Ckr\Util;

use Ckr\Util\ArrayMerger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArrayMergerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->beConstructedWith(array(), array());
        $this->shouldHaveType('Ckr\Util\ArrayMerger');
    }

    // ==========================================================
    // =========== Behaviour with default configuration        ==
    // ==========================================================

    function it_should_overwrite_string_keys_given_default_configuration()
    {
        $default = ['a' => 1];
        $precedence = ['a' => 2];

        $expected = [
            'a' => 2,   // from $precedence
        ];
        $this->beConstructedWith($default, $precedence);
        $result = $this->mergeData();
        $result->shouldBeAnArray();
        $result->shouldHaveSameData($expected);
    }

    function it_should_preserve_a_string_key_given_default_configuration()
    {
        $default = ['a' => 1];
        $precedence = ['b' => 2, 0 => 3]; // should not interfere value of key 'a'
        $expected = ['a' => 1, 'b' => 2, 0 => 3];

        $this->beConstructedWith($default, $precedence);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_append_a_numeric_key_given_default_configuration()
    {
        $default = [1 => 'a'];
        $precedence = [1 => 'b'];
        $expected = [1 => 'a', 2 => 'b'];

        $this->beConstructedWith($default, $precedence);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_work_recursively_given_sub_array_with_same_string_key_and_default_config()
    {
        $default = ['outer' => ['a' => 1, 'b' => 2]];
        $precedence = ['outer' => ['b' => 'new-b', 'c' => 'c']];
        $expected = [
            'outer' => ['a' => 1, 'b' => 'new-b', 'c' => 'c']
        ];
        $this->beConstructedWith($default, $precedence);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_throw_an_exception_on_different_dimensions_given_default_config()
    {
        $default = ['k' => 'value'];
        $precedence = ['k' => ['value']];

        $this->beConstructedWith($default, $precedence);
        $this->shouldThrow('\UnexpectedValueException')->during('mergeData', [$default, $precedence]);
    }

    // ========================================================================
    // =========== Behaviour with flag FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION ==
    // ========================================================================

    function it_should_convert_scalar_to_array_in_default_given_flag_is_set()
    {
        $default = ['k' => 'value']; // will be converted to ['k' => [0 => 'value']]
        $precedence = ['k' => ['othervalue']];
        $flags = ArrayMerger::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION;

        $expected = ['k' => [0 => 'value', 1 => 'othervalue']];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_convert_scalar_to_array_in_precedence_given_flag_is_set()
    {
        $default = ['k' => ['value']];
        $precedence = ['k' => 'othervalue'];
        $flags = ArrayMerger::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION;

        $expected = ['k' => [0 => 'value', 1 => 'othervalue']];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    // ======================================================================
    // ========== behaviour with flag FLAG_OVERWRITE_NUMERIC_KEY      =======
    // ======================================================================

    function it_should_overwrite_numeric_key_given_flag_is_set()
    {
        $default = [1 => 'a'];
        $precedence = [1 => 'b'];
        $flags = ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY;
        $expected = [1 => 'b'];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_overwrite_numeric_key_given_flag_is_set_and_prevent_doubles_flag_is_also_set()
    {
        $default = [1 => 'a', 2 => 'b'];
        $precedence = [1 => 'b'];
        $flags = ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY | ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS;
        $expected = [1 => 'b', 2 => 'b'];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }


    // ======================================================================
    // ========== behaviour with flags FLAG_OVERWRITE_NUMERIC_KEY     =======
    // ==========    and FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION set    =======
    // ======================================================================

    function it_should_overwrite_numeric_key_after_conversion_if_flags_are_set()
    {
        $default = ['k' => 'value'];
        $precedence = ['k' => [0 => 'othervalue']];
        $flags = ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY | ArrayMerger::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION;
        $expected = ['k' => [0 => 'othervalue']];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }


    // ===============================================================================
    // ========== behaviour with flag FLAG_PREVENT_DOUBLE_VALUE_ON_NUMERIC_KEY =======
    // ===============================================================================

    function it_should_skip_existent_key_given_flag_is_set()
    {
        $default = [0 => 'value', 2 => 'x', 3 => 'y', 4 => 'z'];
        $precedence = [1 => 'value', 2 => 'y', 4 => 'z', 100 => 'valid'];
        $flags = ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS;

        $expected = $default;
        $expected[] = 'valid';

        $this->beConstructedWith($default, $precedence, $flags);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    // ===============================================================================
    // ================== examples to check setting flags via setters ================
    // ===============================================================================

    function it_should_allow_setting_allowConversion_flag_via_setter()
    {
        $default = ['k' => 'value'];
        $precedence = ['k' => ['othervalue']];

        $expected = ['k' => [0 => 'value', 1 => 'othervalue']];

        $this->beConstructedWith($default, $precedence);
        $this->allowConversionFromScalarToArray(true)->shouldHaveType('Ckr\Util\ArrayMerger');
        $this->mergeData()->shouldHaveSameData($expected);
    }


    function it_should_allow_unsetting_allowConversion_flag_via_setter()
    {
        $default = ['k' => 'value'];
        $precedence = ['k' => ['othervalue']];
        $flags = ~0; // all bits set

        $this->beConstructedWith($default, $precedence, $flags);
        $this->allowConversionFromScalarToArray(false)->shouldHaveType('Ckr\Util\ArrayMerger');
        $this->shouldThrow('UnexpectedValueException')->during('mergeData');
    }

    function it_should_allow_setting_overwriteNumericKey_flag_via_setter()
    {
        $default = [1 => 'a'];
        $precedence = [1 => 'b'];
        $expected = [1 => 'b'];

        $this->beConstructedWith($default, $precedence);
        $this->overwriteNumericKey(true)->shouldHaveType('\Ckr\Util\ArrayMerger');
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_allow_unsetting_overwriteNumericKey_flag_via_setter()
    {
        $default = [1 => 'a'];
        $precedence = [1 => 'b'];
        $flags = ~0; // all bits set
        $expected = [1 => 'a', 2 => 'b'];

        $this->beConstructedWith($default, $precedence, $flags);
        $this->overwriteNumericKey(false)->shouldHaveType('\Ckr\Util\ArrayMerger');
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_allow_setting_preventDoubleWhenAppending_via_setter()
    {
        $default = [0 => 'value', 2 => 'x', 3 => 'y', 4 => 'z'];
        $precedence = [1 => 'value', 2 => 'y', 4 => 'z', 100 => 'valid'];

        $expected = $default;
        $expected[] = 'valid';

        $this->beConstructedWith($default, $precedence);
        $this->preventDoubleValuesWhenAppendingNumericKeys(true)
            ->shouldHaveType('\Ckr\Util\ArrayMerger');
        $this->mergeData()->shouldHaveSameData($expected);
    }

    function it_should_allow_unsetting_preventDoubleWhenAppending_via_setter()
    {
        $default = [1 => 'a', 99 => 'b'];
        $precedence = [1 => 'b'];
        $expected = [1 => 'a', 99 => 'b', 100 => 'b'];

        $flags = ~0;

        $this->beConstructedWith($default, $precedence, $flags);
        $this->preventDoubleValuesWhenAppendingNumericKeys(false)
            ->shouldHaveType('\Ckr\Util\ArrayMerger');
        $this->overwriteNumericKey(false);
        $this->mergeData()->shouldHaveSameData($expected);
    }

    public function getMatchers()
    {
        return array(
            // checks that two arrays have the same data (ignoring the order of string keys)
            'haveSameData' => function($subject, $expectedArray) {
                foreach ($expectedArray as $key => $valueExpected) {
                    if (!\array_key_exists($key, $subject)) return false;
                    if (\is_array($valueExpected)) {
                        $m = $this->getMatchers();
                        $fnSameData = $m['haveSameData'];
                        $res = $fnSameData($subject[$key], $valueExpected);
                        if (false === $res) return false;
                    } else {
                        if ($valueExpected !== $subject[$key]) return false;
                    }
                    unset($subject[$key]);
                }
                return \count($subject) === 0;
            },
            'beAnArray' => function($subject) {
                return (bool) \is_array($subject);
            }
        );
    }
}
