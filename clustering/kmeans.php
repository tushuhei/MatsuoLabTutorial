<!DOCTYPE html>
<?php
if($_GET['num_points']){$num_points = $_GET['num_points'];}else{$num_points = 30;}
if($_GET['num_keys']){$num_keys = $_GET['num_keys'];}else{$num_keys = 5;}
?>
<html>
<head>
<title>クラスタリング分析ーK-means法</title>
</head>
<body>
<form action="kmeans.php" method="get">
<table>
<tr>
<td colspan="3">
<h2>K-means法</h2>
</td>
</tr>
<tr>
<td>
要素数：
</td>
<td colspan="2">
<input type="text" name="num_points" value=<?php echo $num_points; ?> size="10">
</td>
</tr>
<tr>
<td>
代表点数：
</td>
<td colspan="2">
<input type="text" name="num_keys" value=<?php echo $num_keys; ?> size="10">
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
<td colspan="2" id="navigate">
各要素を代表点に割り当てます
</td>
</tr>
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
  //代表点群の描画
  var num_keys = <?php echo $num_keys; ?>;

  kmeans(num_points, num_keys);
}
function kmeans(num_points, num_keys){ 
  //点群オブジェクト
  var points = new Array(num_points);
  //代表点オブジェクト
  var keys = new Array(num_keys);
  //クエリ用
  var chdxk = '';
  var chdyk = '';
  var chdxp = '';
  var chdyp = '';
  var chcok = '';
  var chcop = '';
  //前回の代表点の座標を格納する変数の宣言
  var chdxk_pre = '';
  var chdyk_pre = '';
  //色ランダム生成用
  var cele = new Array('f','c','9','6','3','0');
  var color = new Array(num_keys);
  //ステップ数
  var step = 0;
  //ボタンの挙動フラグ
  var flag = 0;
  //代表点に色を振り分ける
  for(var i=0; i<num_keys; i++){
    //動的に色配列を生成
    color[i]  ='';
    for(var j=0; j<6; j++){
      color[i] += cele[Math.floor( Math.random() * 6 )];
    }
    //代表点オブジェクトの生成
    keys[i] = Array(4);
    keys[i][0] = Math.floor( Math.random() * 100 ); //x座標
    keys[i][1] = Math.floor( Math.random() * 100 ); //y座標
    //クエリの生成
    chdxk += keys[i][0] + ',';
    chdyk += keys[i][1] + ',';
    chcok += "o," + color[i] + ",0," + i + ",15|";
  }
  //点群の作成
  for(var i=0; i<num_points; i++){
    //点群オブジェクトの生成
    points[i] = Array(4);
    points[i][0] = Math.floor( Math.random() * 100 ); //x座標
    points[i][1] = Math.floor( Math.random() * 100 ); //y座標
    points[i][2] = ''; //所属クラスタ
    //クエリの生成
    chdxp += points[i][0] + ',';
    chdyp += points[i][1] + ',';
    chcop += "s,000000,0," + (i + num_keys) + ",15|";
  }
  //マージしてカンマやパイプを消す調整
  var chdx = (chdxk + chdxp).slice(0,-1);
  var chdy = (chdyk + chdyp).slice(0,-1);
  var chco = (chcok + chcop).slice(0,-1);
  //Chart APIへ投げる
  var chd = chdx + '|' + chdy;
  var chart = document.getElementById("chart"); //グラフ領域
  chart.src = "http://chart.apis.google.com/chart?chs=400x400&chd=t:"+chd+"&cht=s&chm="+chco;
  var step_view = document.getElementById("step"); //ステップ数表示領域
  var navi_view = document.getElementById("navigate"); //ナビゲート領域
  //クリック時の機能
  document.getElementById("changeButton").onclick = function(){
    //要素を各クラスタに振り分ける
    switch(flag){
    case 0:
      var dist;
      //全部の点の距離を算出、最小値を求める
      for(var i=0; i<num_points; i++){
        var min = 10000;
        for(var j=0; j<num_keys; j++){
          //距離の二乗を算出
          dist = Math.pow((points[i][0] - keys[j][0]),2) + Math.pow((points[i][1] - keys[j][1]),2);
          //その点と代表点の距離が最小ならばその代表点クラスタに属させる
          if(dist<min){
            min = dist;
            points[i][2] = j;
          }
        }
      }
      //色変更用クエリ
      chcop = '';
      for(var i=0; i<num_points; i++){
        chcop += "s," + color[points[i][2]] + ",0," + (i + num_keys) + ",15|";
      }
      //マージしてパイプを消す調整
      chco = chcok + chcop;
      chco = chco.slice(0,-1);
      //Chart APIへ投げる
      var chart = document.getElementById("chart");
      var chd = chdx + '|' + chdy;
      chart.src = "http://chart.apis.google.com/chart?chs=400x400&chd=t:"+ chd +"&cht=s&chm="+chco;
      //ステップ数更新
      step++;
      step_view.innerText = "Step: " + step;
      //ナビゲート更新
      navi_view.innerText = "重心を求め、代表点を移動します";
      flag = 1;
      break;
    case 1:
      chdxk = '';
      chdyk = '';
      chcok = '';
      //各クラスタについて重心を求める
      for(var j=0; j<num_keys; j++){
        keys[j][0] = 0;
        keys[j][1] = 0;
        var cnt = 0;
        for(var i=0; i<num_points; i++){
          if(points[i][2] == j){
            keys[j][0] += points[i][0];
            keys[j][1] += points[i][1];
            cnt++;
          }
        }
        //平均を求める。とりあえずの０割対策をとっている。後でじっくり検証
        if(cnt != 0){
          keys[j][0] = keys[j][0] / cnt;
          keys[j][1] = keys[j][1] / cnt;
        }
        //クエリの生成
        chdxk += keys[j][0] + ',';
        chdyk += keys[j][1] + ',';
        chcok += "o," + color[j] + ",0," + j + ",15|";
      }
      //マージしてカンマやパイプを消す調整
      chdx = (chdxk + chdxp).slice(0,-1);
      chdy = (chdyk + chdyp).slice(0,-1);
      chco = (chcok + chcop).slice(0,-1);
      //Chart APIへ投げる
      var chd = chdx + '|' + chdy;
      var chart = document.getElementById("chart");
      chart.src = "http://chart.apis.google.com/chart?chs=400x400&chd=t:"+chd+"&cht=s&chm="+chco;
      //代表点の座標に変化があるか検証
      if((chdxk_pre == chdxk ) && (chdyk_pre == chdyk )){
        alert('クラスタリング完了');
        var result = 0;
        for(var i=0; i<num_points; i++){
          result += Math.pow((points[i][0] - keys[points[i][2]][0]),2) + Math.pow((points[i][1] - keys[points[i][2]][1]),2);
        }
        alert('結果は ' + result + ' でした。');
      }
      //代表点の座標を取っておく
      chdxk_pre = chdxk;
      chdyk_pre = chdyk;

      //ステップ数更新
      step++;
      step_view.innerText = "Step: " + step;
      //ナビゲート更新
      navi_view.innerText = "各要素を代表点に割り当てます";
      flag = 0;

      break;
    }
  }
}
</script>
</html>
