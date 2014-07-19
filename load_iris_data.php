<?php

function load_iris_data($filename)
{
    $lines = file($filename, FILE_IGNORE_NEW_LINES);

    $data = array();
    foreach ($lines as $l) {
        $data[] = parse_line($l);
    }

    return $data;
}

function parse_line($line)
{
    $data = array();
    $fields = explode("\t", $line);

    return array(
        (int) $fields[0],   // id            データ番号
        (float) $fields[1], // sepal.length  がく片の長さ
        (float) $fields[2], // sepal.width   がく片の幅
        (float) $fields[3], // petal.length  花弁の長さ
        (float) $fields[4], // petal.width   花弁の幅
        $fields[5],         // species       品種
    );
}
