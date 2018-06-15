<?php
$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT);
$offset = filter_input(INPUT_POST, 'offset', FILTER_VALIDATE_INT);
$order = strip_tags(trim(filter_input(INPUT_POST, 'order', FILTER_DEFAULT)));
$orderAsc = strip_tags(trim(filter_input(INPUT_POST, 'orderAsc', FILTER_VALIDATE_BOOLEAN)));
$filter = filter_input(INPUT_POST, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$type = strip_tags(trim(filter_input(INPUT_POST, 'type', FILTER_DEFAULT)));
$relation = strip_tags(trim(filter_input(INPUT_POST, 'relation', FILTER_DEFAULT)));
$column = strip_tags(trim(filter_input(INPUT_POST, 'column', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$read = new \Table\TableData($entity);
$read->setLimit($limit);
$read->setPagina($offset);
$read->setOrder($order);
$read->setOrderAsc($orderAsc);
$read->setFilter($filter);

if(!empty($type) && in_array($type, ['owner', 'publisher']) && !empty($id) && !empty($relation) && !empty($column)) {
    $read->setRelation($relation);
    $read->setType($type);
    $read->setColumn($column);
    $read->setId($id);
}

$data['data'] = [];
$data['data']['content'] = $read->getDados();
$data['data']['pagination'] = $read->getPagination();
$data['data']['total'] = $read->getTotal();
