<?php
/**
     * 驼峰转下划线
     *
     * @param  string $value
     * @param  string $delimiter
     * @return string
     */
    // function str_snake(string $value, string $delimiter = '_'): string
    // {
    //     $key = $value;

    //     if (isset(static::$snakeCache[$key][$delimiter])) {
    //         return static::$snakeCache[$key][$delimiter];
    //     }

    //     if (!ctype_lower($value)) {
    //         $value = preg_replace('/\s+/u', '', $value);

    //         $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    //     }

    //     return static::$snakeCache[$key][$delimiter] = $value;
    // }