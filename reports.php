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

    $query = 'SELECT scoredBy, assist, count(*) as count from assists
    group by scoredBy, assist';

    $stmt = $db->prepare($query);
    $stmt->execute(array());
    $results = $stmt->fetchall(\PDO::FETCH_ASSOC);

    $i = 0;

    foreach ($results as $item) {

      $graph['links'][$i]['source'] = $players[$item['assist']];
      $graph['links'][$i]['target'] = $players[$item['scoredBy']];
      $graph['links'][$i]['value'] = intval($item['count'])*5;

      $i++;

    }

    echo json_encode($graph);

    return json_encode($graph);

  }
