<?php

if (!function_exists('precision_floor')) {
  function precision_floor(int|float $num, int $precision = 0)
  {
    $tenPowPrec = 10 ** $precision;
    return floor($num * $tenPowPrec) / $tenPowPrec;
  }
}
