<?php

function get_fields(array $array, array $fields): array {
    $data = [];
    foreach ($fields as $item) {
        $data[$item] = $array[$item] ?? null;
    }

    return $data;
}