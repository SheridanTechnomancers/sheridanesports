<?php
//will pull from main once its pulling correctly.
 require 'sheridananalytics/main.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Analytics</title>
    <link rel="stylesheet" href="css/analytics.css">
  </head>
  <body>
    <main>
      <h1>Summoner Name: <?php echo str_replace("%20"," ",$summonerName); ?></h1>
      <table>
        <tr>
          <th>Placement</th>
          <th>Champion Id</th>
          <th>Gold</th>
          <th>Game Time (h)</th>
          <th>Game Time (m)</th>
          <th>Game Time (s)</th>
          <th>level</th>
          <th>Win Ratio</th>
          <th>Wards Placed</th>
          <th>Kills</th>
          <th>Deaths</th>
          <th>Assists</th>
          <th>KDA</th>
          <th>First Blood</th>
          <th>Damage Dealt</th>
          <th>Games Played</th>
          <th>Average CS delta for 0-10 (m)</th>
          <th>Average CS delta for 10-20 (m)</th>
          <th>Average CS delta for 20-30 (m)</th>
        </tr>
        <tr>
          <?php
          $i=0;
          foreach ($firstChampStats as $key => $value) {
            if($i==0){echo "<td>".$value."</td>";}
            else{echo "<td>".round($value,2)."</td>";}
            $i++;
          }?>
        </tr>
        <tr>
          <?php
          $j=0;
          foreach ($secondChampStats as $key => $value) {
            if($j==0){echo "<td>".$value."</td>";}
            else{echo "<td>".round($value,2)."</td>";}
            $j++;
          }?>
        </tr>
        <tr>
          <?php
          $k=0;
          foreach ($thirdChampStats as $key => $value) {
            if($k==0){echo "<td>".$value."</td>";}
            else{echo "<td>".round($value,2)."</td>";}
            $k++;
          }?>
        </tr>
        <tr>
          <?php
          $l=0;
          foreach ($fourthChampStats as $key => $value) {
            if($l==0){echo "<td>".$value."</td>";}
            else{echo "<td>".round($value,2)."</td>";}
            $l++;
          }?>
        </tr>
        <tr>
          <?php
          $m=0;
          foreach ($fifthChampStats as $key => $value) {
            if($m==0){echo "<td>".$value."</td>";}
            else{echo "<td>".round($value,2)."</td>";}
            $m++;
          }?>
        </tr>
      </table>
    </main>
  </body>
</html>
