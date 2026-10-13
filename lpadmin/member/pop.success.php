<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = isset($mb_id) ? $mb_id : '';
	$mb_id = base64_decode($mb_id2);
	$safe_mb_id = sql_real_escape_string($mb_id);

	$sql = "
		select *
		from g5_member a
		join g5_member_etc b on a.mb_id = b.mb_id
		where a.mb_id = '{$safe_mb_id}'
		limit 1
	";
	$row = sql_fetch($sql);

        if (
                empty($row['mb_id'])
                || (string) $row['mb_id'] !== (string) $mb_id
        ) {
                alert('회원을 찾을 수 없습니다.');
                exit;
        }

        $login_mb_id = isset($member['mb_id'])
                ? trim((string) $member['mb_id'])
                : '';

        $login_level = isset($member['mb_level'])
                ? (int) $member['mb_level']
                : 0;

        if (!lottoCanViewMember(
                $login_mb_id,
                $login_level,
                $mb_id
        )) {
                alert('접근 권한이 없는 회원입니다.');
                exit;
        }

        $member_token = lottoMemberTokenCreate();

        $current_run = sql_fetch(
                "select draw_no
                   from l_filter_run
                  where status = 'filtered'
                    and candidate_count > 0
                  order by draw_no desc
                  limit 1",
                false
        );
        $current_draw_no = isset($current_run['draw_no'])
                ? (int) $current_run['draw_no']
                : 0;

	$member_turns = array();

        if ($current_draw_no > 0) {
                $member_turns[] = $current_draw_no;
        }

	$turn_result = sql_query(
		"select distinct draw_no
		   from l_member_combination
		  where mb_id = '{$safe_mb_id}'
		  order by draw_no desc",
		false
	);

	while ($turn_row = sql_fetch_array($turn_result)) {
		$draw_no = (int) $turn_row['draw_no'];

		if ($draw_no > 0 && !in_array($draw_no, $member_turns, true)) {
			$member_turns[] = $draw_no;
		}
	}

	$turn = isset($_GET['turn']) ? (int) $_GET['turn'] : 0;

	if ($turn < 1 && !empty($member_turns)) {
		$turn = (int) $member_turns[0];
	}

	/*
	 * 회원 당첨 이력은 현재 결과 처리 기준인 l_member_draw에서 조회한다.
	 *
	 * 기존 g5_member_etc.lucky1 ~ lucky5 값은 현재 결과 처리에서
	 * 갱신하지 않으므로 배분당첨 화면의 집계 기준으로 사용하지 않는다.
	 */
	$winning_summary = array(
		1 => array('count' => 0, 'draws' => array()),
		2 => array('count' => 0, 'draws' => array()),
		3 => array('count' => 0, 'draws' => array()),
		4 => array('count' => 0, 'draws' => array()),
		5 => array('count' => 0, 'draws' => array()),
	);

	$winning_result = sql_query(
		"select
			draw_no,
			rank1_count,
			rank2_count,
			rank3_count,
			rank4_count,
			rank5_count
		   from l_member_draw
		  where mb_id = '{$safe_mb_id}'
		    and (
				rank1_count > 0
				or rank2_count > 0
				or rank3_count > 0
				or rank4_count > 0
				or rank5_count > 0
		    )
		  order by draw_no desc",
		false
	);

	if ($winning_result === false) {
		alert('회원 당첨이력을 조회하지 못했습니다.');
		exit;
	}

	while ($winning_row = sql_fetch_array($winning_result)) {
		$winning_draw_no = isset($winning_row['draw_no'])
			? (int) $winning_row['draw_no']
			: 0;

		if ($winning_draw_no < 1) {
			continue;
		}

		for ($rank = 1; $rank <= 5; $rank++) {
			$rank_column = 'rank' . $rank . '_count';

			$rank_count = isset($winning_row[$rank_column])
				? (int) $winning_row[$rank_column]
				: 0;

			if ($rank_count < 1) {
				continue;
			}

			$winning_summary[$rank]['count'] += $rank_count;

			$winning_summary[$rank]['draws'][] = array(
				'draw_no' => $winning_draw_no,
				'count' => $rank_count,
			);
		}
	}

	$list = getLuckyNum($turn);
	$luckAry = array($list['drwtNo1'], $list['drwtNo2'],$list['drwtNo3'],$list['drwtNo4'],$list['drwtNo5'],$list['drwtNo6']);
	//print_r($luckAry);

?>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12 col-12">
			<!-- general form elements -->
			<div class="card card-primary">
				<?php include_once("./member.head.php");?>
				<!-- /.card-header -->
				<!-- form start -->
				<div class="row align-items-start">
					<div class="col-2" style="padding:10px 20px 0px">
						<form id="turnForm">
							<input type="hidden" name="mb_id" value="<?=base64_encode($mb_id)?>">
							<select id="turn" name="turn" class="form-control select2 select2-hidden-accessible" style="width: 100%;" aria-hidden="true" onChange="$('#turnForm').submit();">
								<?php foreach ($member_turns as $member_turn) { ?>
								<option value="<?=$member_turn?>" <?php if ($turn === $member_turn) { echo "selected"; }?>><?=$member_turn?> 회차</option>
								<?php } ?>
							</select>
						</form>
					</div>
					<div class="col-6" style="padding:10px 20px 0px">
						<div>
							<?php for ($rank = 1; $rank <= 5; $rank++) { ?>
								<?=$rank?>등 당첨 :
								<?php if ($winning_summary[$rank]['count'] > 0) { ?>
								<button
									type="button"
									class="btn btn-link btn-sm p-0 winning-count-toggle"
									data-target="winning-rank-<?=$rank?>"
									style="vertical-align:baseline;"
								>
									<?=number_format($winning_summary[$rank]['count'])?>
								</button>
								<?php } else { ?>
								0
								<?php } ?>

								<?php if ($rank < 5) { ?>
								/
								<?php } ?>
							<?php } ?>
						</div>

						<?php for ($rank = 1; $rank <= 5; $rank++) { ?>
							<?php if ($winning_summary[$rank]['count'] > 0) { ?>
							<div
								id="winning-rank-<?=$rank?>"
								class="winning-draw-list mt-2 p-2 border rounded bg-light"
								style="display:none;"
							>
								<strong><?=$rank?>등 당첨 회차 :</strong>

								<?php foreach ($winning_summary[$rank]['draws'] as $winning_draw) { ?>
									<?php
									$winning_draw_no =
										(int) $winning_draw['draw_no'];

									$winning_draw_count =
										(int) $winning_draw['count'];

									$winning_draw_url =
										'./pop.success.php?mb_id='
										. urlencode(base64_encode($mb_id))
										. '&turn='
										. $winning_draw_no;
									?>
									<a
										href="<?=htmlspecialchars(
											$winning_draw_url,
											ENT_QUOTES,
											'UTF-8'
										)?>"
										class="badge <?=$turn === $winning_draw_no
											? 'badge-primary'
											: 'badge-light border'?> mr-1"
										style="font-size:13px;"
									>
										<?=$winning_draw_no?>회<?php
										if ($winning_draw_count > 1) {
											echo ' (' . $winning_draw_count . '건)';
										}
										?>
									</a>
								<?php } ?>
							</div>
							<?php } ?>
						<?php } ?>
					</div>
                                        <div class="col-4" style="padding:10px 20px 0px">
                                                <div class="d-flex justify-content-end">
                                                        <form method="post"
                                                              action="pop.success.action.php"
                                                              class="mr-2"
                                                              onsubmit="return confirm('회원정보의 로또 배분 수량만큼 당회차 조합을 부여하시겠습니까?');">
                                                                <input type="hidden" name="token" value="<?=htmlspecialchars($member_token, ENT_QUOTES, 'UTF-8')?>">
                                                                <input type="hidden" name="mb_id" value="<?=htmlspecialchars($mb_id, ENT_QUOTES, 'UTF-8')?>">
                                                                <input type="hidden" name="action" value="assign">
                                                                <button type="submit" class="btn btn-primary btn-sm">당회차 부여하기</button>
                                                        </form>
                                                        <form method="post"
                                                              action="pop.success.action.php"
                                                              onsubmit="return confirm('이 회원의 당회차 정규 조합과 추가 조합을 모두 회수하시겠습니까? 회수하면 해당 조합은 문자 발송 대상에서도 제외됩니다.');">
                                                                <input type="hidden" name="token" value="<?=htmlspecialchars($member_token, ENT_QUOTES, 'UTF-8')?>">
                                                                <input type="hidden" name="mb_id" value="<?=htmlspecialchars($mb_id, ENT_QUOTES, 'UTF-8')?>">
                                                                <input type="hidden" name="action" value="revoke">
                                                                <button type="submit" class="btn btn-danger btn-sm">당회차 회수하기</button>
                                                        </form>
                                                </div>
                                        </div>
				</div>

				<script>
				$(document).on(
					'click',
					'.winning-count-toggle',
					function()
					{
						var targetId =
							$(this).data('target');

						$('.winning-draw-list')
							.not('#' + targetId)
							.hide();

						$('#' + targetId).toggle();
					}
				);
				</script>

					<div class="row">
						<div class="col-md-12 col-12">
							<div class="card-body">
								<div class="form-group">
									<table class="table table-hover text-nowrap text-sm">
									<thead>
									<tr>
										<th>NO</th>
										<th>회차</th>
										<th>등급</th>
										<th>조합번호</th>
										<th>배분시간</th>
										<th>결과</th>
									</tr>
									</thead>
									<tbody>
									</tbody>
									<?php
										$sql_search = "
											where mb_id = '{$safe_mb_id}'
											  and draw_no = '{$turn}'
										";

										$row2 = sql_fetch(
											"select count(*) as cnt
											   from l_member_combination
											   {$sql_search}",
											false
										);
										$total_count = (int) $row2['cnt'];

										$rows = 10;
										$total_page = $total_count > 0 ? (int) ceil($total_count / $rows) : 1;
										$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

										if ($page < 1) {
											$page = 1;
										}

										$from_record = ($page - 1) * $rows;

										$sql2 = "select
												lmc_id,
												draw_no,
												member_type,
												num1, num2, num3, num4, num5, num6,
												result_rank,
												result_checked_at,
												created_at
											from l_member_combination
											{$sql_search}
											order by lmc_id desc
											limit {$from_record}, {$rows}";

										$result2 = sql_query($sql2, false);
										for($i=0; $row2 = sql_fetch_array($result2); $i++){
											$ball_text = "";
											$ball_text = $row2['num1'].",".$row2['num2'].",".$row2['num3'].",".$row2['num4'].",".$row2['num5'].",".$row2['num6'];
									?>
									<tr>
										<td><?=$total_count-($page-1)*$rows-$i?></td>
										<td><?=(int) $row2['draw_no']?></td>
										<td><?=htmlspecialchars($row2['member_type'], ENT_QUOTES, 'UTF-8')?></td>
									<td><?=htmlspecialchars($ball_text, ENT_QUOTES, 'UTF-8')?></td>
									<td><?=htmlspecialchars($row2['created_at'], ENT_QUOTES, 'UTF-8')?></td>
									<td><?php
									        if (empty($row2['result_checked_at'])) {
									                echo '추첨대기';
									        } elseif (
									                $row2['result_rank'] !== null
									                && (int) $row2['result_rank'] >= 1
									                && (int) $row2['result_rank'] <= 5
									        ) {
									                echo (int) $row2['result_rank'] . '등';
									        } else {
									                echo '낙첨';
									        }
									?></td>
									</tr>
									<?php 	}?>
									<?php if($total_count < 1){?>
									<tr>
										<td colspan="10" style="text-align:center;">배분된 조합이 없습니다.</td>
									</tr>
									<?php }?>
									</table>

									<?php
										$qstr .="&mb_id=".base64_encode($mb_id)."&turn=".$turn;
										echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page=');
									?>
								</div>
							</div>
						</div>
					</div>
					<!-- row 끝-->
				</div>
				<!-- /.card-body -->
		</div>
	</div>
</section>
<!-- /.card -->
