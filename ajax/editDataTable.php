<?php

$table = strip_tags(trim(filter_input(INPUT_POST, 'table', FILTER_DEFAULT)));
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
