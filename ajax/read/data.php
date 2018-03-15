<?php

$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT);
$offset = filter_input(INPUT_POST, 'offset', FILTER_VALIDATE_INT);
$order = strip_tags(trim(filter_input(INPUT_POST, 'order', FILTER_DEFAULT)));
$orderAsc = strip_tags(trim(filter_input(INPUT_POST, 'orderAsc', FILTER_VALIDATE_BOOLEAN)));
$filter = filter_input(INPUT_POST, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$read = new \Table\TableData($entity);
$read->setLimit($limit);
$read->setPagina($offset);
$read->setOrder($order);
$read->setOrderAsc($orderAsc);
$read->setFilter($filter);

$data['data'] = [];
$data['data']['content'] = $read->getDados();
$data['data']['pagination'] = $read->getPagination();
$data['data']['total'] = $read->getTotal();
