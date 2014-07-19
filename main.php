<?php

require_once __DIR__ . '/load_iris_data.php';
require_once __DIR__ . '/decision_tree.php';
require_once __DIR__ . '/random_forest.php';

function main()
{
    $iris_data_set = load_iris_data(__DIR__ . '/iris.txt');

    shuffle($iris_data_set);
    $iris_training_set = array_slice($iris_data_set, 0, 100);
    $iris_test_set = array_slice($iris_data_set, 100, 50);

    $trees = rf_train($iris_training_set, 30);
    var_dump($trees);

    $result = array();
    $accuracy = 0;

    foreach ($iris_test_set as $i) {
        $species = random_forest_classify($trees, $i);
        $k = $i[5] . ',' . $species;
        if (!array_key_exists($k, $result)) {
            $result[$k] = 0;
        }
        ++$result[$k];
        if ($i[5] == $species) {
            ++$accuracy;
        }
    }
    foreach ($result as $k => $c) {
        echo "$k,$c\n";
    }
    echo ($accuracy * 100 / count($iris_test_set)) . "%\n";
}

main();
