<?php

require_once __DIR__ . '/decision_tree.php';

function rf_train($data, $m)
{
    $trees = array();
    for ($i = 0; $i < $m; ++$i) {
        $sample = sample_data($data, 50);
        $features = sample_feature(4, 2);
        $tree = train_tree($sample, $features);
        // var_dump($tree);
        $trees[] = $tree;
    }

    return $trees;
}

// $data の中から $samples 個をランダムに選択する
function sample_data($data, $samples)
{
    $index = sample_index(count($data), $samples);

    $result = array();
    foreach ($index as $i) {
        $result[] = $data[$i];
    }

    return $result;
}

function sample_feature($total, $samples)
{
    $result = sample_index($total, $samples);
    foreach ($result as &$i) {
        ++$i;
    }

    return $result;
}

// $total 個の中から $samples 個のインデックスを取得する
function sample_index($total, $samples)
{
    // TODO: これだと重複無しになる
    $result = range(0, $total - 1);
    shuffle($result);
    $result = array_slice($result, $samples);

    return $result;
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
