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

function train_tree($sample, $features)
{
    assert('count($sample) > 0');

    $species = get_uniq_species($sample);
    if (count($species) == 1) {
        return new DT_TerminalNode(array_keys($species)[0]);
    }

    $best_feature = 0;
    $best_threshold = 0;
    $best_left = array();
    $best_right = array();
    $best_gain = -99999999;

    foreach ($features as $fi) {
        $d = array();
        foreach ($sample as $s) {
            // $d[$i] = $sample[$i][$fi]
            $d[] = $s[$fi];
        }
        // それを 値の昇順に key を維持してソートする
        asort($d);
        // $sorted_index は $sample の index を対象説明変数の値の昇順にソートしたもの
        $sorted_index = array_keys($d);

        $left = array();
        $right = array();
        $limit = count($sorted_index);
        for ($i = $limit - 1; $i >= 0; --$i) {
            $right[] = $sample[$sorted_index[$i]];
        }

        $curr = array_pop($right);
        $left[] = $curr;
        $prev = $curr;
        for ($i = 1; $i < $limit; ++$i) {
            $curr = $sample[$sorted_index[$i]];

            if ($prev[$fi] < $curr[$fi]) {
                $gain = calc_gain($left, $right);
                if ($gain > $best_gain) {
                    $best_feature = $fi;
                    $best_threshold = $curr[$fi];
                    $best_left = $left;
                    $best_right = $right;
                    $best_gain = $gain;
                }
            }

            $check = array_pop($right);
            $left[] = $curr;
            if ($check[0] != $curr[0]) {
                echo "error! " . $check[0] . " vs " . $curr[0] . "\n";
                exit;
            }
            $prev = $curr;
        }
    }

    if (count($best_left) == 0 || count($best_right) == 0) {
        // TODO 説明変数が同一で分けられない
        return new DT_TerminalNode($sample[0][5]);
    }

    return new DT_QueryNode(
        $best_feature,
        $best_threshold,
        train_tree($best_left, $features),
        train_tree($best_right, $features));
}

function get_uniq_species($sample)
{
    return array_count_values(array_map(function ($x) { return $x[5]; }, $sample));
}

function calc_gain($left, $right)
{
    $lcount = count($left);
    $rcount = count($right);

    $lprob = $lcount / ($lcount + $rcount);
    $rprob = $rcount / ($lcount + $rcount);

    $lspecies = get_uniq_species($left);
    $rspecies = get_uniq_species($right);

    $lentropy = calc_entropy($lspecies);
    $rentropy = calc_entropy($rspecies);

    $result = -($lprob * $lentropy + $rprob * $rentropy);
    // echo "c=$lcount,$rcount, p=$lprob,$rprob, e=$lentropy,$rentropy, gain=$result\n";

    return $result;
}

function calc_entropy($counts)
{
    $total = array_sum($counts);
    $result = 0;
    foreach ($counts as $c) {
        $prob = $c / $total;
        $result -= $prob * log($prob);
    }

    return $result;
}
