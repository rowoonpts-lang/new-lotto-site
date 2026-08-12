<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$loginLevel = isset($member['mb_level'])
		? (int) $member['mb_level']
		: 0;

	if (!lottoIsStaffLevel($loginLevel)) {
		alert('회원등록 권한이 없습니다.');
	}

	$canCreateStaff = lottoCanCreateStaff($loginLevel);
?>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12 col-12">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">회원추가</h3>
					</div>

					<form
						name="frm"
						id="frm"
						method="post"
						autocomplete="off"
						action="member.save.php"
						onSubmit="return fnSubmit();"
					>
						<div class="card-body">

							<div class="form-group">
								<label for="mb_id">아이디</label>
								<input
									type="text"
									class="form-control"
									id="mb_id"
									name="mb_id"
									maxlength="20"
									required
								>
							</div>

							<div class="form-group">
								<label for="mb_password">패스워드</label>
								<input
									type="text"
									class="form-control"
									id="mb_password"
									name="mb_password"
									required
								>
							</div>

							<?php if ($canCreateStaff) { ?>
							<div class="form-group">
								<label for="mb_level">권한설정</label>
								<select
									class="form-control"
									id="mb_level"
									name="mb_level"
								>
									<option value="2">무료회원</option>
									<option value="<?=LOTTO_ROLE_STAFF1?>">직원1</option>
									<option value="<?=LOTTO_ROLE_STAFF2?>">직원2</option>
									<option value="<?=LOTTO_ROLE_TEAM_LEADER?>">팀장</option>
									<option value="<?=LOTTO_ROLE_ADMIN?>">관리자</option>
								</select>
							</div>
							<?php } else { ?>
							<input type="hidden" name="mb_level" value="2">
							<?php } ?>

							<div class="form-group">
								<label for="mb_name">이름(실명)</label>
								<input
									type="text"
									class="form-control"
									id="mb_name"
									name="mb_name"
									required
								>
							</div>

							<div class="form-group">
								<label for="mb_hp">핸드폰번호</label>
								<input
									type="text"
									class="form-control"
									id="mb_hp"
									name="mb_hp"
									maxlength="11"
									placeholder="01012345678"
									required
								>
								<small class="text-danger">
									01012345678 형식으로 입력해주세요.
								</small>
							</div>

						</div>

						<div class="card-footer text-center">
							<button type="submit" class="btn btn-primary">
								등록
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function fnSubmit()
{
	var mbId = $.trim($("#mb_id").val());
	var mbPassword = $("#mb_password").val();
	var mbName = $.trim($("#mb_name").val());
	var mbHp = $("#mb_hp").val().replace(/[^0-9]/g, '');

	$("#mb_id").val(mbId);
	$("#mb_name").val(mbName);
	$("#mb_hp").val(mbHp);

	if (mbId === '') {
		alert('아이디를 입력해주세요.');
		$("#mb_id").focus();
		return false;
	}

	if (mbPassword === '') {
		alert('패스워드를 입력해주세요.');
		$("#mb_password").focus();
		return false;
	}

	if (mbName === '') {
		alert('이름을 입력해주세요.');
		$("#mb_name").focus();
		return false;
	}

	if (!/^01[0-9][0-9]{7,8}$/.test(mbHp)) {
		alert('핸드폰번호를 숫자만 입력해주세요.');
		$("#mb_hp").focus();
		return false;
	}

	return true;
}
</script>
