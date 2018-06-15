<?php
$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$type = strip_tags(trim(filter_input(INPUT_POST, 'type', FILTER_DEFAULT)));
$relation = strip_tags(trim(filter_input(INPUT_POST, 'relation', FILTER_DEFAULT)));
$column = strip_tags(trim(filter_input(INPUT_POST, 'column', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($entity && !empty($entity)) {
    $table = new \Table\Table($entity);
    if(!empty($type) && in_array($type, ['owner', 'publisher']) && !empty($id) && !empty($relation) && !empty($column)) {
        $table->setRelation($relation);
        $table->setType($type);
        $table->setColumn($column);
        $table->setId($id);
    }

    $data['data'] = $table->getShow();
}