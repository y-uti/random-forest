<?php

require_once __DIR__ . '/decision_tree.php';

function random_forest_train($data, $m)
{
    $trees = array();
    for ($i = 0; $i < $m; ++$i) {
        $sample = sample_data($data, 50);
        $features = sample_feature(4, 2);
        $trees[] = train_tree($sample, $features);
    }

    return $trees;
}

// $data の中から $samples 個をランダムに選択する
function sample_data($data, $samples)
{
    $index = sample_index(count($data), $samples);

    return array_map(
        function ($i) use ($data) { return $data[$i]; },
        $index);
}

function sample_feature($total, $samples)
{
    $index = sample_index($total, $samples);

    return array_map(
        function ($i) { return $i + 1; },
        $index);
}

// $total 個の中から $samples 個のインデックスを取得する
function sample_index($total, $samples)
{
    // TODO: これだと重複無しになる
    $result = range(0, $total - 1);
    shuffle($result);

    return array_slice($result, 0, $samples);
}


function random_forest_classify($trees, $data)
{
    $candidates = array_map(
        function ($t) use ($data) { return $t->species($data); },
        $trees);

    return vote($candidates);
}

function vote($candidates)
{
    $result = array_count_values($candidates);
    arsort($result);

    return array_keys($result)[0];
}
