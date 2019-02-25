<?php

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = strip_tags(trim(filter_input(INPUT_POST, 'status', FILTER_VALIDATE_BOOLEAN)));
$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$col = strip_tags(trim(filter_input(INPUT_POST, 'col', FILTER_DEFAULT)));
$dados = [$col => $status === "1" ? 1 : 0, "id" => $id];

$read = new \ConnCrud\Read();
$read->exeRead($entity, "WHERE id = :id", "id={$id}");
if($read->getResult()) {
    $oldDados = $read->getResult()[0];
}

$up = new \ConnCrud\Update();
$up->exeUpdate($entity, $dados, "WHERE id = :id", "id={$id}");
new \Entity\React("update", $entity, $dados, $oldDados);
