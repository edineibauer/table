<?php

$table = strip_tags(trim(filter_input(INPUT_POST, 'table', FILTER_DEFAULT)));
$limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT);
$pagina = filter_input(INPUT_POST, 'offset', FILTER_VALIDATE_INT);
$order = strip_tags(trim(filter_input(INPUT_POST, 'order', FILTER_DEFAULT)));
$orderAsc = strip_tags(trim(filter_input(INPUT_POST, 'orderAsc', FILTER_VALIDATE_BOOLEAN)));
$filter = $_POST['filter'] ?? null;

$read = new \TableList\ReadTable($table);
$read->setLimit($limit);
$read->setPagina($pagina);
$read->setOrder($order);
$read->setOrderAsc($orderAsc);
$read->setFilter($filter);

echo json_encode(array("response" => $read->isResponse(), "data" => $read->getDados(), "pagination" => $read->getPagination()));