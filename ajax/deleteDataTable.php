<?php

$table = strip_tags(trim(filter_input(INPUT_POST, 'table', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

if($table && $id) {
    $entity = new \Entity\Entity($table);
    if(is_array($id)) {
        foreach ($id as $item) {
            $entity->delete($item);
        }
    } else {
        $entity->delete($id);
    }
}