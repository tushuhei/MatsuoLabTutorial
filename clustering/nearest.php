<!DOCTYPE html>
<?php
if($_GET['num_points']){$num_points = $_GET['num_points'];}else{$num_points = 30;}
if($_GET['num_clusters']){$num_clusters = $_GET['num_clusters'];}else{$num_clusters = 5;}
?>
<html>
<head>
<title>クラスタリング分析ー単連結法</title>
</head>
<body>
<form action="clustering.php" method="get">
<table>
<tr>
<td colspan="3">
<h2>単連結法(最短距離法)</h2>
</td>
</tr>
<tr>
<td>
要素数：
</td>
<td>
<input type="text" name="num_points" value=<?php echo $num_points; ?> size="10">
</td>
</tr>
<tr>
<td>
クラスター数：
</td>
<td>
<input type="text" name="num_clusters" value=<?php echo $num_clusters; ?> size="10">
</td>
<td>
<input type="submit" name="submit" value="restart">
</td>
</tr>
</table>
</form>
<img id="chart">
<form>
<table>
<tr>
<td>
<input type="button" value="NEXT" id="changeButton">
</td>
<td id="step">
Step: 0
</td>
</tr>
</table>
</form>
</body>
<script type="text/javascript">
window.onload = function(){
  //初期点群の描画
  var num_points = <?php echo $num_points; ?>;
  //初期点群の描画
  var num_clusters = <?php echo $num_clusters; ?>;
  //点群オブジェクト
  var points = new Array(num_points);
  //クエリ用
  var chdx = '';
  var chdy = '';
  var chco = '';
  //色ランダム生成用
  var cele = new Array('f','c','9','6','3','0');
  var color = new Array(num_points);
  //ステップ数
  var step = 0;

  for(var i=0; i<num_points; i++){
    //動的に色配列を生成
    color[i]  ='';
    for(var j=0; j<6; j++){
      color[i] += cele[Math.floor( Math.random() * 6 )];
    }
    //点群オブジェクトの生成
    points[i] = Array(4);
    points[i][0] = Math.floor( Math.random() * 100 ); //x座標
    points[i][1] = Math.floor( Math.random() * 100 ); //y座標
    points[i][2] = i; //所属クラスタ
    points[i][3] = 1; //所属クラスタに所属する要素数
    //クエリの生成
    chdx += points[i][0] + ',';
    chdy += points[i][1] + ',';
    chco += "o," + color[points[i][2]] + ",0," + i + ",15|";
  }
  //コンマやパイプを消す調整
  chdx = chdx.slice(0,-1);
  chdy = chdy.slice(0,-1);
  chco = chco.slice(0,-1);
  //Chart APIへ投げる
  var chd = chdx + '|' + chdy;
  var chart = document.getElementById("chart");
  chart.src = "http://chart.apis.google.com/chart?chs=400x400&chd=t:"+chd+"&cht=s&chm="+chco;
  var step_view = document.getElementById("step");
  //クリック時の機能
  document.getElementById("changeButton").onclick = function(){
    var min = 10000;
    var min_i = 0;
    var min_j = 0;
    var dist;
    //全部の点の距離を算出、最小値を求める
    for(var i=0; i<num_points; i++){
      for(var j=0; j<num_points-i-1; j++){
        dist = Math.pow((points[i][0] - points[j+i+1][0]),2) + Math.pow((points[i][1] - points[j+i+1][1]),2);
        if((dist<min)&&(points[i][2] != points[j+i+1][2])){
          min = dist;
          min_i = i;
          min_j = j+i+1;
        }
      }
    }
    //クラスを統合する。
    if(points[min_j][3] > points[min_i][3]){
      points[min_i][2] = points[min_j][2];
    }else{
      points[min_j][2] = points[min_i][2];
    }
    //クラスタ構成員のカウントを増やす
    points[min_i][3]++;
    points[min_j][3]++;

    var variation = new Array();
    chco = '';
    for(var i=0; i<num_points; i++){
      //色変更用クエリ
      chco += "o," + color[points[i][2]] + ",0," + i + ",15|";
      //クラスター数検証のための下準備でfor文を借りる。
      variation[i] = points[i][2];
    }
    //クラスター数検証
    var uniq_clusters = unique(variation);

    if(num_clusters == uniq_clusters.length){
      alert('クラスタリング完了');
      var result = 0;
      /*
        for(var i=0; i<num_points; i++){
          for(var j=0; j<num_clusters; j++){
            result += Math.pow((points[i][0] - keys[points[i][2]][0]),2) + Math.pow((points[i][1] - keys[points[i][2]][1]),2);
          }
    }
       */
      //各クラスターの重心を求める
      var grav = new Array();
      for(var i=0; i<num_clusters; i++){
        grav[i] = new Array();
        grav[i][1] = 0;
        grav[i][2] = 0;
        grav[i][3] = 0;
        grav[i][0] = uniq_clusters[i];
      }
      for(var i=0; i<num_points; i++){
        for(var j=0; j<num_clusters; j++){
          if(grav[j][0] == points[i][2]){
            grav[j][1] += points[i][0];
            grav[j][2] += points[i][1];
            grav[j][3]++;
          }
        }
      }
      for(var i=0; i<num_clusters; i++){
        grav[i][1] = grav[i][1]/grav[i][3];
        grav[i][2] = grav[i][2]/grav[i][3];
      }
      for(var i=0; i<num_points; i++){
        for(var j=0; j<num_clusters; j++){
          if(points[i][2] == grav[j][0]){
            result += Math.pow((points[i][0] - grav[j][1]),2) + Math.pow((points[i][1] - grav[j][2]),2);
          }
        }
      }
      alert('結果は' + result + 'でした');
    }
  //パイプを消す調整
  chco = chco.slice(0,-1);
  //Chart APIへ投げる
  var chart = document.getElementById("chart");
  chart.src = "http://chart.apis.google.com/chart?chs=400x400&chd=t:"+chd+"&cht=s&chm="+chco;
  step++;
  step_view.innerText = "Step: " + step;
}

//配列のユニークな要素を返すための関数
function unique(array) {
  var storage = {};
  var uniqueArray = [];

  var i, value;
  for (i = 0; i < array.length; i++) {
    value = array[i];
    if (!(value in storage)) {
      storage[value] = true;
      uniqueArray.push(value);
    }
  }

  return uniqueArray;
}
}
</script>
</html>
