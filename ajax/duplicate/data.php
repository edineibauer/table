<?php
$entity = strip_tags(trim(filter_input(INPUT_POST, 'entity', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$new = \Entity\Entity::copy($entity, $id);
if ($new)
    \Entity\Entity::add($entity, $new);