<?php

require_once __DIR__ . '/../vendor/autoload.php';

$entity = new ppEntity\BasicEntity('testEntity');
$entity->value1 = 'testValue1';
$entity->value2 = 'testValue2';
$entity->value3 = 123;
$entity->save();


$ent2 = new ppEntity\BasicEntity('testEntity', 1);
print_r($ent2);
