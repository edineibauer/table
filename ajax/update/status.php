<?php

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = strip_tags(trim(filter_input(INPUT_POST, 'status', FILTER_VALIDATE_BOOLEAN)));
$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$col = strip_tags(trim(filter_input(INPUT_POST, 'col', FILTER_DEFAULT)));

$up = new \ConnCrud\Update();
$up->exeUpdate($entity, [$col => ($status ? 1 : 0)], "WHERE id = :id", "id={$id}");