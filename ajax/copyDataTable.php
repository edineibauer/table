<?php

$table = strip_tags(trim(filter_input(INPUT_POST, 'table', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$entity = new \Entity\Entity($table);
if(is_array($id)) {
    foreach ($id as $item) {
        $entity->duplicate($item);
        $entity->save();
    }
} else {
    $entity->duplicate($id);
    $entity->save();
}