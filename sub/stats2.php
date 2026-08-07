<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 확률과 조합 분석';

// LottoGPT 전용 전체 폭 레이아웃
$lottogpt_full_width_page = true;

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

include_once(G5_PATH . "/_head.php");
?>

<script>
document.body.classList.add('lottogpt-page');
</script>

<main class="lg-combination-page">

    <section class="lg-combination-hero">
        <div class="lg-shell">

            <p class="lg-eyebrow">LOTTOGPT COMBINATION LAB</p>

            <h1>
                로또의 확률과 조합을<br>
                <strong>숫자로 확인하세요.</strong>
            </h1>

            <p class="lg-combination-hero-copy">
                로또 6/45의 전체 조합은 8,145,060개입니다.
                LottoGPT는 전체 조합을 여러 기준으로 나누어
                확률과 분포를 이해하기 쉽게 정리합니다.
            </p>

            <div class="lg-combination-summary">

                <article>
                    <span>전체 조합</span>
                    <strong>8,145,060</strong>
                    <small>45개 중 6개 선택</small>
                </article>

                <article>
                    <span>1등 확률</span>
                    <strong>1 / 8,145,060</strong>
                    <small>모든 조합의 확률은 동일</small>
                </article>

                <article>
                    <span>평균 번호 합</span>
                    <strong>138</strong>
                    <small>6개 번호 합의 중심값</small>
                </article>

            </div>

        </div>
    </section>

    <section class="lg-combination-section">
        <div class="lg-shell">

            <div class="lg-combination-heading">
                <p class="lg-eyebrow">COMBINATION DATA</p>
                <h2>조합 기준별 데이터</h2>
                <p>
                    번호 개수, 당첨 등수, 번호 합, 홀짝 구성 등
                    여러 기준에 따른 전체 조합의 분포입니다.
                </p>
            </div>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>01</span>
                    <div>
                        <h3>번호 개수별 완전조합수</h3>
                        <p>선택한 번호 개수에서 만들 수 있는 6개 번호 조합의 수입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>개수</th>
                                <th>조합수</th>
                                <th>개수</th>
                                <th>조합수</th>
                                <th>개수</th>
                                <th>조합수</th>
                                <th>개수</th>
                                <th>조합수</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>6</td><td>1</td>
                                <td>16</td><td>8,008</td>
                                <td>26</td><td>230,230</td>
                                <td>36</td><td>1,947,792</td>
                            </tr>
                            <tr>
                                <td>7</td><td>7</td>
                                <td>17</td><td>12,376</td>
                                <td>27</td><td>296,010</td>
                                <td>37</td><td>2,324,784</td>
                            </tr>
                            <tr>
                                <td>8</td><td>28</td>
                                <td>18</td><td>18,564</td>
                                <td>28</td><td>376,740</td>
                                <td>38</td><td>2,760,681</td>
                            </tr>
                            <tr>
                                <td>9</td><td>84</td>
                                <td>19</td><td>27,132</td>
                                <td>29</td><td>475,020</td>
                                <td>39</td><td>3,262,623</td>
                            </tr>
                            <tr>
                                <td>10</td><td>210</td>
                                <td>20</td><td>38,760</td>
                                <td>30</td><td>593,775</td>
                                <td>40</td><td>3,838,380</td>
                            </tr>
                            <tr>
                                <td>11</td><td>462</td>
                                <td>21</td><td>54,264</td>
                                <td>31</td><td>736,281</td>
                                <td>41</td><td>4,496,388</td>
                            </tr>
                            <tr>
                                <td>12</td><td>924</td>
                                <td>22</td><td>74,613</td>
                                <td>32</td><td>906,192</td>
                                <td>42</td><td>5,245,786</td>
                            </tr>
                            <tr>
                                <td>13</td><td>1,716</td>
                                <td>23</td><td>100,947</td>
                                <td>33</td><td>1,107,568</td>
                                <td>43</td><td>6,096,454</td>
                            </tr>
                            <tr>
                                <td>14</td><td>3,003</td>
                                <td>24</td><td>134,596</td>
                                <td>34</td><td>1,344,904</td>
                                <td>44</td><td>7,059,052</td>
                            </tr>
                            <tr>
                                <td>15</td><td>5,005</td>
                                <td>25</td><td>177,100</td>
                                <td>35</td><td>1,623,160</td>
                                <td>45</td><td>8,145,060</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>02</span>
                    <div>
                        <h3>등수별 당첨확률</h3>
                        <p>6개 번호 한 조합을 기준으로 한 경우의 수와 확률입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>등수</th>
                                <th>일치 조건</th>
                                <th>경우의 수</th>
                                <th>확률</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="lg-combination-highlight">1등</td>
                                <td>당첨번호 6개</td>
                                <td>1</td>
                                <td>8,145,060 : 1</td>
                            </tr>
                            <tr>
                                <td>2등</td>
                                <td>당첨번호 5개 + 보너스번호</td>
                                <td>6</td>
                                <td>1,357,510 : 1</td>
                            </tr>
                            <tr>
                                <td>3등</td>
                                <td>당첨번호 5개</td>
                                <td>228</td>
                                <td>35,724 : 1</td>
                            </tr>
                            <tr>
                                <td>4등</td>
                                <td>당첨번호 4개</td>
                                <td>11,115</td>
                                <td>약 733 : 1</td>
                            </tr>
                            <tr>
                                <td>5등</td>
                                <td>당첨번호 3개</td>
                                <td>182,780</td>
                                <td>약 45 : 1</td>
                            </tr>
                            <tr>
                                <td>-</td>
                                <td>당첨번호 2개</td>
                                <td>1,233,765</td>
                                <td>약 6.6 : 1</td>
                            </tr>
                            <tr>
                                <td>-</td>
                                <td>당첨번호 1개</td>
                                <td>3,454,542</td>
                                <td>약 2.4 : 1</td>
                            </tr>
                            <tr>
                                <td>-</td>
                                <td>당첨번호 0개</td>
                                <td>3,262,623</td>
                                <td>약 2.5 : 1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>03</span>
                    <div>
                        <h3>총합별 조합수</h3>
                        <p>6개 번호의 합계에 따라 존재하는 조합의 수입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>조합의 합</th>
                                <th>해당 조합수</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>21 (최소)</td><td>1</td></tr>
                            <tr><td>100</td><td>50,236</td></tr>
                            <tr><td>106</td><td>62,621</td></tr>
                            <tr>
                                <td class="lg-combination-highlight">138 (평균)</td>
                                <td class="lg-combination-highlight">105,690</td>
                            </tr>
                            <tr><td>170</td><td>62,621</td></tr>
                            <tr><td>176</td><td>50,236</td></tr>
                            <tr><td>255 (최대)</td><td>1</td></tr>
                            <tr class="lg-combination-total">
                                <td>합계</td>
                                <td>8,145,060</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>04</span>
                    <div>
                        <h3>홀짝별 조합수</h3>
                        <p>6개 번호에 포함된 홀수와 짝수 개수별 조합 분포입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>홀수</th>
                                <th>짝수</th>
                                <th>조합수</th>
                                <th>비율</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>0</td><td>6</td><td>74,613</td><td>0.92%</td></tr>
                            <tr><td>1</td><td>5</td><td>605,682</td><td>7.44%</td></tr>
                            <tr><td>2</td><td>4</td><td>1,850,695</td><td>22.72%</td></tr>
                            <tr>
                                <td>3</td><td>3</td>
                                <td class="lg-combination-highlight">2,727,340</td>
                                <td class="lg-combination-highlight">33.48%</td>
                            </tr>
                            <tr><td>4</td><td>2</td><td>2,045,505</td><td>25.11%</td></tr>
                            <tr><td>5</td><td>1</td><td>740,278</td><td>9.09%</td></tr>
                            <tr><td>6</td><td>0</td><td>100,947</td><td>1.24%</td></tr>
                            <tr class="lg-combination-total">
                                <td colspan="2">합계</td>
                                <td>8,145,060</td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>05</span>
                    <div>
                        <h3>저고별 조합수</h3>
                        <p>1~22를 낮은 수, 23~45를 높은 수로 구분한 조합 분포입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>낮은 수</th>
                                <th>높은 수</th>
                                <th>조합수</th>
                                <th>비율</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>0</td><td>6</td><td>100,947</td><td>1.24%</td></tr>
                            <tr><td>1</td><td>5</td><td>740,278</td><td>9.09%</td></tr>
                            <tr><td>2</td><td>4</td><td>2,045,505</td><td>25.11%</td></tr>
                            <tr>
                                <td>3</td><td>3</td>
                                <td class="lg-combination-highlight">2,727,340</td>
                                <td class="lg-combination-highlight">33.48%</td>
                            </tr>
                            <tr><td>4</td><td>2</td><td>1,850,695</td><td>22.72%</td></tr>
                            <tr><td>5</td><td>1</td><td>605,682</td><td>7.44%</td></tr>
                            <tr><td>6</td><td>0</td><td>74,613</td><td>0.92%</td></tr>
                            <tr class="lg-combination-total">
                                <td colspan="2">합계</td>
                                <td>8,145,060</td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>06</span>
                    <div>
                        <h3>끝수별 조합수</h3>
                        <p>각 번호의 일의 자리 숫자가 얼마나 겹치는지에 따른 분포입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>끝수 형태</th>
                                <th>조합수</th>
                                <th>비율</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>끝수가 모두 다른 경우</td><td>1,708,100</td><td>20.9%</td></tr>
                            <tr><td>2개의 끝수가 같은 경우</td><td>5,708,120</td><td>70.0%</td></tr>
                            <tr><td>3개의 끝수가 같은 경우</td><td>705,040</td><td>8.6%</td></tr>
                            <tr><td>4개의 끝수가 같은 경우</td><td>23,600</td><td>0.3%</td></tr>
                            <tr><td>5개의 끝수가 같은 경우</td><td>200</td><td>0.002%</td></tr>
                            <tr><td>6개의 끝수가 같은 경우</td><td>0</td><td>0%</td></tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="lg-combination-panel">

                <div class="lg-combination-panel-head">
                    <span>07</span>
                    <div>
                        <h3>연번별 조합수</h3>
                        <p>연속된 번호가 포함되는 조합의 수와 전체 대비 비율입니다.</p>
                    </div>
                </div>

                <div class="lg-combination-table-scroll">
                    <table class="lg-combination-table">
                        <thead>
                            <tr>
                                <th>연번 형태</th>
                                <th>조합수</th>
                                <th>비율</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>2연번인 경우</td><td>3,848,260</td><td>47.25%</td></tr>
                            <tr><td>3연번인 경우</td><td>425,620</td><td>5.22%</td></tr>
                            <tr><td>4연번인 경우</td><td>31,200</td><td>0.38%</td></tr>
                            <tr><td>5연번인 경우</td><td>1,560</td><td>0.02%</td></tr>
                            <tr><td>6연번인 경우</td><td>40</td><td>0.0005%</td></tr>
                            <tr class="lg-combination-total">
                                <td>합계</td>
                                <td>4,306,680</td>
                                <td>52.87%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </article>

            <div class="lg-combination-notice">
                <div>
                    <span>DATA NOTE</span>
                    <h2>조합의 형태와 당첨 확률은 다릅니다.</h2>
                </div>

                <p>
                    모든 6개 번호 조합은 동일한 1등 당첨 확률을 가집니다.
                    특정 홀짝 구성, 번호 합, 연속번호 형태가 많이 존재한다는 사실이
                    개별 번호 조합의 미래 당첨 가능성을 높여주는 것은 아닙니다.
                </p>
            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
