<?php

namespace Ckr\Util;

class ArrayMerger
{

    /**
     * Given the default array has a scalar 'value' at key 'k' and
     * the precedence array has a sub array at the same key 'k'.
     *
     * If flag is set, the default scalar value 'value' is wrapped in
     * an array [0 => 'value'] before the merge is done.
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

    /**
     * @var array
     */
    protected $default;

    /**
     * @var array
     */
    protected $precedence;

    /**
     * @var int
     */
    protected $flags;

    public function __construct(array $default, array $precedence, $flags = 0)
    {
        $this->default = $default;
        $this->precedence = $precedence;
        $this->flags = $flags;
    }

    public function mergeData()
    {
        $precedence = $this->precedence;
        $default = $this->default;

        return static::doMerge($default, $precedence, $this->flags);
    }

    public static function doMerge(array $default, array $precedence, $flags = 0)
    {
        foreach ($precedence as $key => $pVal) {
            if (\is_numeric($key) && (0 === ($flags & self::FLAG_OVERWRITE_NUMERIC_KEY))) {
                if (0 === ($flags & self::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS)
                    || !\in_array($pVal, $default)
                ) {
                    $default[] = $pVal;
                }
                continue;
            }
            if (\array_key_exists($key, $default)) {
                $default[$key] = static::mergeRecursively($default[$key], $pVal, $flags);
            } else {
                $default[$key] = $pVal;
            }
        }
        return $default;
    }

    /**
     * Setter for flag FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION
     *
     * @param bool $flagAllowConversion
     *
     * @return $this
     */
    public function allowConversionFromScalarToArray($flagAllowConversion)
    {
        if ($flagAllowConversion) {
            $this->setFlag(self::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION);
        } else {
            $this->unsetFlag(self::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION);
        }
        return $this;
    }

    /**
     * Setter for flag FLAG_OVERWRITE_NUMERIC_KEY
     *
     * @param $flagOverwrite
     *
     * @return $this
     */
    public function overwriteNumericKey($flagOverwrite)
    {
        if ($flagOverwrite) {
            $this->setFlag(self::FLAG_OVERWRITE_NUMERIC_KEY);
        } else {
            $this->unsetFlag(self::FLAG_OVERWRITE_NUMERIC_KEY);
        }
        return $this;
    }

    /**
     * Setter for flag FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS
     *
     * @param $flag
     *
     * @return $this
     */
    public function preventDoubleValuesWhenAppendingNumericKeys($flag)
    {
        if ($flag) {
            $this->setFlag(self::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS);
        } else {
            $this->unsetFlag(self::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS);
        }
        return $this;
    }

    protected static function mergeRecursively($default, $precedence, $flags)
    {
        if (\is_array($default) && \is_array($precedence)) {
            return static::doMerge($default, $precedence, $flags);
        }
        if (! \is_array($default) && ! \is_array($precedence)) {
            return $precedence; // overwrite default by precedence
        }
        if (! ($flags & self::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION)) {
            throw new \UnexpectedValueException('different dimensions');
        }
        if (! \is_array($default)) {
            $default = array(0 => $default);
        } else {
            $precedence = array(0 => $precedence);
        }
        return static::doMerge($default, $precedence, $flags);
    }

    private function setFlag($flag)
    {
        $this->flags = $this->flags | $flag;
    }

    private function unsetFlag($flag)
    {
        $this->flags = $this->flags & ~$flag;
    }

}
