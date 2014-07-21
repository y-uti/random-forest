<?php

require_once __DIR__ . '/decision_tree.php';

function random_forest_train($data, $m, $n_features)
{
    $trees = array();
    for ($i = 0; $i < $m; ++$i) {
        $sample = sample_data($data);
        $trees[] = train_tree($sample, $n_features);
    }

    return $trees;
}

function sample_data($data, $n = 0)
{
    $count = count($data);

    if ($n <= 0) {
        $n = $count;
    }

    $result = array();
    for ($i = 0; $i < $n; ++$i) {
        $result[] = $data[mt_rand(0, $count - 1)];
    }

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
