<?php
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>
<ul class="s3_ul">
	<li><a href="<?=G5_URL?>/sub/sub0301.php">로또 분석용어</a></li>
	<li class="active"><a href="<?=G5_URL?>/sub/sub0302.php">확률과 조합 분석</a></li>
	<li><a href="<?=G5_URL?>/sub/sub0303.php">로또 구입 잘하는 방법</a></li>
</ul>
<div class="s3_tit">확률과 조합 분석</div>
<section id="s32">
	<article class="s32_1">
		<div class="stats2">
	
			<div class="sts_tb_box">
				<p class="sts_tit">번호갯수별 완전조합수</p>
				<div class="sts_scr">
					<table class="sts_tb sts_tbbd">
						<tr>
							<th style="width:12.5%;">갯수</th>
							<th style="width:12.5%;">조합수</th>
							<th style="width:12.5%;">갯수</th>
							<th style="width:12.5%;">조합수</th>
							<th style="width:12.5%;">갯수</th>
							<th style="width:12.5%;">조합수</th>
							<th style="width:12.5%;">갯수</th>
							<th style="width:12.5%;">조합수</th>
						</tr>
						<tr>
							<td>6</td>
							<td>1</td>
							<td>16</td>
							<td>8,008</td>
							<td>26</td>
							<td>230,230</td>
							<td>36</td>
							<td>1,947,792</td>
						</tr>
						<tr>
							<td>7</td>
							<td>7</td>
							<td>17</td>
							<td>12,376</td>
							<td>27</td>
							<td>296,010</td>
							<td>37</td>
							<td>2,324,784</td>
						</tr>
						<tr>
							<td>8</td>
							<td>28</td>
							<td>18</td>
							<td>18,564</td>
							<td>28</td>
							<td>376,740</td>
							<td>38</td>
							<td>2,760,681</td>
						</tr>
						<tr>
							<td>9</td>
							<td>84</td>
							<td>19</td>
							<td>27,132</td>
							<td>29</td>
							<td>475,020</td>
							<td>39</td>
							<td>3,262,623</td>
						</tr>
						<tr>
							<td>10</td>
							<td>210</td>
							<td>20</td>
							<td>38,760</td>
							<td>30</td>
							<td>593,775</td>
							<td>40</td>
							<td>3,838,380</td>
						</tr>
						<tr>
							<td>11</td>
							<td>462</td>
							<td>21</td>
							<td>54,264</td>
							<td>31</td>
							<td>736,281</td>
							<td>41</td>
							<td>4,496,388</td>
						</tr>
						<tr>
							<td>12</td>
							<td>924</td>
							<td>22</td>
							<td>74,613</td>
							<td>32</td>
							<td>906,192</td>
							<td>42</td>
							<td>5,245,786</td>
						</tr>
						<tr>
							<td>13</td>
							<td>1,716</td>
							<td>23</td>
							<td>100,947</td>
							<td>33</td>
							<td>1,107,568</td>
							<td>43</td>
							<td>6,096,454</td>
						</tr>
						<tr>
							<td>14</td>
							<td>3,003</td>
							<td>24</td>
							<td>134,596</td>
							<td>34</td>
							<td>1,344,904</td>
							<td>44</td>
							<td>7,059,052</td>
						</tr>
						<tr>
							<td>15</td>
							<td>5,005</td>
							<td>25</td>
							<td>177,100</td>
							<td>35</td>
							<td>1,623,160</td>
							<td>45</td>
							<td>8,145,060</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">등수별 당첨확률</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:25%;">등수</th>
							<th style="width:25%;">당첨 갯수</th>
							<th style="width:25%;">경우의 수</th>
							<th style="width:25%;">당첨확률</th>
						</tr>
						<tr>
							<td>1등</td>
							<td>당첨볼 6개</td>
							<td>1</td>
							<td>8,145,060:1</td>
						</tr>
						<tr>
							<td>2등</td>
							<td>당첨볼 5개+보너스번호</td>
							<td>6</td>
							<td>1,357,510:1</td>
						</tr>
						<tr>
							<td>3등</td>
							<td>당첨볼 5개</td>
							<td>228</td>
							<td>35,724:1</td>
						</tr>
						<tr>
							<td>4등</td>
							<td>당첨볼 4개</td>
							<td>11,109</td>
							<td>733:1</td>
						</tr>
						<tr>
							<td>5등</td>
							<td>당첨볼 3개</td>
							<td>182,774</td>
							<td>45:1</td>
						</tr>
						<tr>
							<td>-</td>
							<td>당첨볼 2개</td>
							<td>1,233,759</td>
							<td>6.6:1</td>
						</tr>
						<tr>
							<td>-</td>
							<td>당첨볼 1개</td>
							<td>3,454,536</td>
							<td>2.4:1</td>
						</tr>
						<tr>
							<td>-</td>
							<td>당첨볼 0개</td>
							<td>3,262,623</td>
							<td>2.5:1</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">총합별 조합수</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:50%;">조합의 합</th>
							<th style="width:50%;">해당 조합수</th>
						</tr>
						<tr>
							<td>21(최소)</td>
							<td>1</td>
						</tr>
						<tr>
							<td>100</td>
							<td>50,236</td>
						</tr>
						<tr>
							<td>106</td>
							<td>62,621</td>
						</tr>
						<tr>
							<td>138(평균)</td>
							<td>105.690</td>
						</tr>
						<tr>
							<td>170</td>
							<td>62,621</td>
						</tr>
						<tr>
							<td>178</td>
							<td>50,236</td>
						</tr>
						<tr>
							<td>255(최대)</td>
							<td>1</td>
						</tr>
						<tr>
							<td>합계</td>
							<td>8,145,060</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">홀짝별 조합수</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:25%;">홀수</th>
							<th style="width:25%;">짝수</th>
							<th style="width:25%;">조합수</th>
							<th style="width:25%;">비율(%)</th>
						</tr>
						<tr>
							<td>0</td>
							<td>6</td>
							<td>74,613</td>
							<td>0.92%</td>
						</tr>
						<tr>
							<td>1</td>
							<td>5</td>
							<td>605,682</td>
							<td>7.44%</td>
						</tr>
						<tr>
							<td>2</td>
							<td>4</td>
							<td>1,850,695</td>
							<td>22.72%</td>
						</tr>
						<tr>
							<td>3</td>
							<td>3</td>
							<td>2,727,340</td>
							<td>33.48%</td>
						</tr>
						<tr>
							<td>4</td>
							<td>2</td>
							<td>2,045,505</td>
							<td>25.11%</td>
						</tr>
						<tr>
							<td>5</td>
							<td>1</td>
							<td>740,278</td>
							<td>9.09%</td>
						</tr>
						<tr>
							<td>6</td>
							<td>0</td>
							<td>100,947</td>
							<td>1.24%</td>
						</tr>
						<tr>
							<td colspan="2">합계</td>
							<td>8,145,060개	</td>
							<td>100%</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">저고별 조합수</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:25%;">낮은 수(저)</th>
							<th style="width:25%;">높은 수(고)</th>
							<th style="width:25%;">조합수</th>
							<th style="width:25%;">비율(%)</th>
						</tr>
						<tr>
							<td>0</td>
							<td>6</td>
							<td>100,947</td>
							<td>1.24%</td>
						</tr>
						<tr>
							<td>1</td>
							<td>5</td>
							<td>740,278</td>
							<td>9.09%</td>
						</tr>
						<tr>
							<td>2</td>
							<td>4</td>
							<td>2,045,505</td>
							<td>25.11%</td>
						</tr>
						<tr>
							<td>3</td>
							<td>3</td>
							<td>2,727,340</td>
							<td>33.48%</td>
						</tr>
						<tr>
							<td>4</td>
							<td>2</td>
							<td>1,850,695</td>
							<td>22.72%</td>
						</tr>
						<tr>
							<td>5</td>
							<td>1</td>
							<td>605,682</td>
							<td>7.44%</td>
						</tr>
						<tr>
							<td>6</td>
							<td>0</td>
							<td>74,613</td>
							<td>0.92%</td>
						</tr>
						<tr>
							<td colspan="2">합계</td>
							<td>8,145,060개	</td>
							<td>100%</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">끝수별 조합수</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:50%;">끝수 형태</th>
							<th style="width:25%;">조합수</th>
							<th style="width:25%;">비율(%)</th>
						</tr>
						<tr>
							<td>끝수가 모두 다른 경우</td>
							<td>1,708,100</td>
							<td>20.9%</td>
						</tr>
						<tr>
							<td>2개의 끝수가 같은 경우	</td>
							<td>5,708,120</td>
							<td>70.0%</td>
						</tr>
						<tr>
							<td>3개의 끝수가 같은 경우	</td>
							<td>705,040</td>
							<td>8.6%</td>
						</tr>
						<tr>
							<td>4개의 끝수가 같은 경우	</td>
							<td>23,600</td>
							<td>0.3%</td>
						</tr>
						<tr>
							<td>5개의 끝수가 같은 경우	</td>
							<td>200</td>
							<td>-</td>
						</tr>
						<tr>
							<td>6개의 끝수가 같은 경우	</td>
							<td>0</td>
							<td>0%</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="sts_tb_box">
				<p class="sts_tit">연번별 조합수</p>
				<div class="sts_scr">
					<table class="sts_tb">
						<tr>
							<th style="width:33.33%;">연번 형태</th>
							<th style="width:33.33%;">조합수</th>
							<th style="width:33.33%;">비율(%)</th>
						</tr>
						<tr>
							<td>2연번인 경우</td>
							<td>3,848,260</td>
							<td>47.25%</td>
						</tr>
						<tr>
							<td>3연번인 경우</td>
							<td>425,620</td>
							<td>5.22%</td>
						</tr>
						<tr>
							<td>4연번인 경우</td>
							<td>31,200</td>
							<td>0.38%</td>
						</tr>
						<tr>
							<td>5연번인 경우</td>
							<td>1,560</td>
							<td>0.01%</td>
						</tr>
						<tr>
							<td>6연번인 경우</td>
							<td>40</td>
							<td>-</td>
						</tr>
						<tr>
							<td>합계</td>
							<td>4,306,680</td>
							<td>52.87%</td>
						</tr>
					</table>
				</div>
			</div>

		</div>
	</article>
</section>

<?php
	include_once(G5_THEME_PATH.'/tail.php');
?>