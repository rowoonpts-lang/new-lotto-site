<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<section class="map">
	
	<ul class="map_ul map_1 active">
		<li><h2>본사</h2></li>
		<li><b>A</b>&nbsp;(03011)서울특별시 종로구 평창8길 12 알파B/D</li>
		<li><b>T</b>&nbsp;(02)395-0960</li>
		<li class="map_bus">
			<b>대중교통 이용</b>
			<p>지하철 광화문역(5호선) 하차 > (교보빌딩 앞) 버스1020(화정박물관) 하차</p>
			<p>지하철 경복궁역(3호선) 하차 > 버스1020, 1711(화정박물관) 하차</p>
			<p>지하철 홍제역(3호선)하차 > 버스110(화정박물관) 하차</p>
			<p>지하철 불광역(3호선)하차 > 버스7211(화정박물관) 하차</p>
			<p>화정박물관 하차 후 오른쪽 방향으로 100m 도보 도착</p>
		</li>
		<li>
			<div id="daumRoughmapContainer1590631092687" class="root_daum_roughmap root_daum_roughmap_landing"></div>
		</li>
	</ul>	

	<ul class="map_ul map_2">
		<li><h2>공장</h2></li>
		<li><b>A</b>&nbsp;(03011)서울특별시 종로구 평창문화로 16</li>
		<li><b>T</b>&nbsp;(02)395-0960</li>
		<li><b>F</b>&nbsp;(02)395-0961</li>
		<li>
			<div id="daumRoughmapContainer1590629346064" class="root_daum_roughmap root_daum_roughmap_landing"></div>
		</li>
	</ul>
	
</section>

<script charset="UTF-8" class="daum_roughmap_loader_script" src="https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js"></script>

<!-- 지도 실행 스크립트 -->
<script charset="UTF-8">
	new daum.roughmap.Lander({
		"timestamp" : "1590629346064",
		"key" : "yjvx",
		"mapHeight" : "490"
	}).render();
</script>

<script charset="UTF-8">
	new daum.roughmap.Lander({
		"timestamp" : "1590631092687",
		"key" : "yjxa",
		"mapHeight" : "490"
	}).render();
</script>

<script>
	$(document).ready(function(){
		/*$('#mab_btn_1').click(function(e){
			e.preventDefault();
			$('.map_1').show();
			$('.map_2').hide();
		});
		$('#mab_btn_2').click(function(e){
			e.preventDefault();
			$('.map_2').show();
			$('.map_1').hide();
		});*/		
	});

	function mapTab(v){
		$('.map_ul').removeClass('active');
		$('.map_'+v).addClass('active');
		$('.map_tab_li').removeClass('active');
		$('.map_tab_li_'+v).addClass('active');
	}
</script>

<?
	include_once(G5_PATH."/_tail.php");
?>
