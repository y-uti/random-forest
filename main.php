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

    $trees = random_forest_train($iris_training_set, 30);

    $truth = array_map(
        function ($i) { return $i[5]; },
        $iris_test_set);

    $estimated = array_map(
        function ($i) use ($trees) { return random_forest_classify($trees, $i); },
        $iris_test_set);

    $confusion_matrix = build_confusion_matrix($truth, $estimated);
    foreach ($confusion_matrix as $key => $count) {
        echo "$key,$count\n";
    }

    echo 'accuracy = ' . calc_accuracy($confusion_matrix) . "\n";
}

function build_confusion_matrix($truth, $estimated)
{
    return array_count_values(
        array_map(
            function ($t, $e) { return "$t,$e"; }, $truth, $estimated));
}

function calc_accuracy($confusion_matrix)
{
    $correct = 0;
    foreach ($confusion_matrix as $key => $count) {
        list ($truth, $estimated) = explode(',', $key);
        if ($truth == $estimated) {
            $correct += $count;
        }
    }

    return $correct / array_sum($confusion_matrix);
}

main();
