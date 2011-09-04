<?php

$script_info = <<<HERE
There is no automatic upgrade from your current database schema (0) to version 1.
Please follow instructions in UPGRADE to complete this.
HERE;

function canupgrade()
{
  return false;
}

function upgrade()
{
  die('Automatic upgrade not supported from version 0 to 1.');
}
