<?php

  $db = new PDO(
      'mysql:host=' . $_SERVER['RDS_HOSTNAME'] . ';dbname=titans',
      $_SERVER['RDS_USERNAME'],
      $_SERVER['RDS_PASSWORD']
  );

  getGraph($db);

  function getGraph($db) {

    $query = 'SELECT * from player';

    $stmt = $db->prepare($query);
    $stmt->execute(array());
    $results = $stmt->fetchall(\PDO::FETCH_ASSOC);

    $players = [];

    $graph = [];

    foreach ($results as $player) {
      $players[$player['number']] = $player['first'];
    }

      $i = 0;

    foreach ($players as $number => $player) {
      $graph['nodes'][$i]['id'] = $player;
      $graph['nodes'][$i]['group'] = $number;
      $i++;
    }

    $query = 'SELECT player1, player2 from assists';

    $stmt = $db->prepare($query);
    $stmt->execute(array());
    $results = $stmt->fetchall(\PDO::FETCH_ASSOC);

    $i = 0;

    // Normalize couples so that first is always greater

    $couples = [];

    foreach ($results as $item) {

      if ($item['player2'] > $item['player1']) {

        $couples[$i]['player1'] = $item['player2'];
        $couples[$i]['player2'] = $item['player1'];

      } else {

        $couples[$i]['player1'] = $item['player1'];
        $couples[$i]['player2'] = $item['player2'];

      }

      $i++;

    }

    // Create a new array of counts of each couple

    $counts = [];

    $i = 0;
    $count= 0;

    foreach ($couples as $couple) {

      $count = 0;

      $counts[$i]['player1'] = $couple['player1'];
      $counts[$i]['player2'] = $couple['player2'];

      foreach($couples as $couple2) {

        if ($couple2['player1'] == $couple['player1'] && $couple2['player2'] == $couple['player2']) {

          $count++;

        }

      }

      $counts[$i]['count'] = $count;

      $i++;

    }

    $i = 0;

    foreach ($counts as $item) {

      $graph['links'][$i]['source'] = $players[$item['player1']];
      $graph['links'][$i]['target'] = $players[$item['player2']];
      $graph['links'][$i]['value'] = intval($item['count']**2);

      $i++;

    }

    echo json_encode($graph);

    return json_encode($graph);

  }
