<?php

/**
 * エラーが出ず、最後に改行されるvar_dump
 *
 * @param * $var
 * @return int
 */
function dump($var)
{
  try {
    var_dump($var);
  } catch (Exception $e) {
    echo $e . "\n";
    return 1;
  }
  echo "\n";
  return 0;
}

/** 現在unixtimeを返すが、time()でよい */
function now()
{
  return time();
}
