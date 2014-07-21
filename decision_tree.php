<?php

abstract class DT_Node
{
    abstract function species($data);
}

class DT_TerminalNode extends DT_Node
{
    private $species;

    function __construct($species)
    {
        $this->species = $species;
    }

    function species($data)
    {
        return $this->species;
    }
}

class DT_QueryNode extends DT_Node
{
    private $featureId;
    private $threshold;
    private $left;
    private $right;

    function __construct($featureId, $threshold, $left, $right)
    {
        $this->featureId = $featureId;
        $this->threshold = $threshold;
        $this->left = $left;
        $this->right = $right;
    }

    function species($data)
    {
        $child = $this->query($data) ? $this->left : $this->right;
        return $child->species($data);
    }

    private function query($data)
    {
        return $data[$this->featureId] < $this->threshold;
    }
}

class DT_BestQuery
{
    private static $NO_DATA = -1;

    private $featureId;
    private $threshold;
    private $left;
    private $right;
    private $gain;

    static function init()
    {
        return new DT_BestQuery(self::$NO_DATA, 0, array(), array(), 0);
    }

    function __construct($featureId, $threshold, $left, $right, $gain)
    {
        $this->featureId = $featureId;
        $this->threshold = $threshold;
        $this->left = $left;
        $this->right = $right;
        $this->gain = $gain;
    }

    function getFeatureId()
    {
        return $this->featureId;
    }

    function getThreshold()
    {
        return $this->threshold;
    }

    function getLeft()
    {
        return $this->left;
    }

    function getRight()
    {
        return $this->right;
    }

    function isImprovedBy($gain)
    {
        return $this->gain < $gain || ! $this->hasData();
    }

    function hasData()
    {
        return $this->featureId != self::$NO_DATA;
    }
}

function train_tree($sample, $n_features)
{
    assert('count($sample) > 0');

    $species = count_by_species($sample);
    if (count($species) == 1) {
        // すべてのサンプルの品種が同じになっていたら終了
        return new DT_TerminalNode(array_keys($species)[0]);
    }

    $features = sample_feature(4, $n_features);

    $best = DT_BestQuery::init();
    foreach ($features as $f) {
        $left = array();
        $right = sort_sample_by_feature_desc($sample, $f);

        $left[] = array_pop($right);
        while ($right) {
            $curr = end($right);
            if (end($left)[$f] < $curr[$f]) {
                $gain = calc_information_gain(array($left, $right));
                if ($best->isImprovedBy($gain)) {
                    $best = new DT_BestQuery(
                        $f, $curr[$f], $left, $right, $gain);
                }
            }
            $left[] = array_pop($right);
        }
    }

    if (!$best->hasData()) {
        // TODO 説明変数が同一で分けられない
        return new DT_TerminalNode($sample[0][5]);
    }

    return new DT_QueryNode(
        $best->getFeatureId(),
        $best->getThreshold(),
        train_tree($best->getLeft(), $n_features),
        train_tree($best->getRight(), $n_features));
}

function sort_sample_by_feature_desc($sample, $f)
{
    usort(
        $sample,
        function ($a, $b) use ($f) {
            $cmp = $b[$f] - $a[$f];
            return $cmp < 0 ? -1 : ($cmp == 0 ? 0 : 1);
        });

    return $sample;
}

function count_by_species($sample)
{
    return array_count_values(array_map(
        function ($i) { return $i[5]; }, $sample));
}

function calc_information_gain($children)
{
    $total = array_sum(array_map(
        function ($c) { return count($c); }, $children));

    $result = array_sum(array_map(
        function ($c) use ($total) {
            return count($c) / $total * calc_entropy($c);
        },
        $children));

    return 0 - $result;
}

function calc_entropy($sample)
{
    $counts = count_by_species($sample);
    $total = array_sum($counts);

    return -1 * array_sum(array_map(
        function ($c) use ($total) {
            $prob = $c /$total;
            return $prob * log($prob);
        },
        $counts));
}

function sample_feature($total, $samples)
{
    $indices = range(0, $total - 1);
    shuffle($indices);
    $indices = array_slice($indices, 0, $samples);

    return array_map(
        function ($i) { return $i + 1; },
        $indices);
}
