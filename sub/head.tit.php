<?php
$sub_top_bg = isset($sub_top_bg) ? (string) $sub_top_bg : '';
$s01_li2 = isset($s01_li2) ? (string) $s01_li2 : '';
$sub_top_li1 = isset($sub_top_li1) ? (string) $sub_top_li1 : '';
$sub_top_li2 = isset($sub_top_li2) ? (string) $sub_top_li2 : '';
$sub_top_li3 = isset($sub_top_li3) ? (string) $sub_top_li3 : '';
$sub_tit = isset($sub_tit) ? (string) $sub_tit : '';
$inner_x = isset($inner_x) ? (string) $inner_x : '';
$bo_table = isset($bo_table) ? (string) $bo_table : '';
$co_id = isset($co_id) ? (string) $co_id : '';

	switch($basename){

		/* sub0101 */
		case "sub0101.php":
			$inner_x = "inner_x";
		break;

		case "sub0102.php":
			$inner_x = "inner_x";
		break;

		case "sub0201.php":
			$inner_x = "inner_x";
		break;

		case "faq.php":
			$s01_li2 = "고객지원";
			$sub_top_li1 = "<li><a href='/bbs/board.php?bo_table=notice'>공지사항</a></li>";
			$sub_top_li2 = "<li class='active'><a href='/bbs/faq.php?fm_id=1'>자주묻는 질문</a></li>";
			$sub_top_li3 = "<li><a href='/bbs/qalist.php'>1:1 상담</a></li>";
			$sub_tit = "자주묻는 질문";
			$sub_top_bg = "sub_top_bg1 lg-support-hero";
		break;

		case "qalist.php": case "qawrite.php": case "qaview.php":
			$s01_li2 = "고객지원";
			$sub_top_li1 = "<li><a href='/bbs/board.php?bo_table=notice'>공지사항</a></li>";
			$sub_top_li2 = "<li><a href='/bbs/faq.php?fm_id=1'>자주묻는 질문</a></li>";
			$sub_top_li3 = "<li class='active'><a href='/bbs/qalist.php'>1:1 상담</a></li>";
			$sub_tit = "1:1상담";
			$sub_top_bg = "sub_top_bg1 lg-support-hero";
		break;

		case "login.php":
			$s01_li2 = "로그인";
			$sub_top_li1 = "<li class='active'><a href='/bbs/login.php'>로그인</a></li>";
			$sub_top_li2 = "<li><a href='/bbs/register.php'>회원가입</a></li>";
			$sub_tit = "로그인";
		break;

		case "register.php":
			$s01_li2 = "회원가입";
			$sub_top_li1 = "<li><a href='/bbs/login.php'>로그인</a></li>";
			$sub_top_li2 = "<li class='active'><a href='/bbs/register.php'>회원가입</a></li>";
			$sub_tit = "회원가입";
		break;

		case "stats.php":
			$s01_li2 = "통계분석실";
			$sub_top_li1 = "<li class='active'><a href='/sub/stats.php'>로또 분석용어</a></li>";
			$sub_top_li2 = "<li><a href='/sub/stats2.php'>확률과 조합 분석</a></li>";
			$sub_top_li3 = "<li><a href='/sub/stats3.php'>로또 이용 가이드</a></li>";
			$sub_tit = "로또 분석용어";
			$sub_top_bg = "sub_top_bg4";
		break;

		case "stats2.php":
			$s01_li2 = "통계분석실";
			$sub_top_li1 = "<li><a href='/sub/stats.php'>로또 분석용어</a></li>";
			$sub_top_li2 = "<li class='active'><a href='/sub/stats2.php'>확률과 조합 분석</a></li>";
			$sub_top_li3 = "<li><a href='/sub/stats3.php'>로또 이용 가이드</a></li>";
			$sub_tit = "확률과 조합 분석";
			$sub_top_bg = "sub_top_bg4";
		break;

		case "stats3.php":
			$s01_li2 = "통계분석실";
			$sub_top_li1 = "<li><a href='/sub/stats.php'>로또 분석용어</a></li>";
			$sub_top_li2 = "<li><a href='/sub/stats2.php'>확률과 조합 분석</a></li>";
			$sub_top_li3 = "<li class='active'><a href='/sub/stats3.php'>로또 이용 가이드</a></li>";
			$sub_tit = "로또 이용 가이드";
			$sub_top_bg = "sub_top_bg4";
		break;

            case "my_lotto.php":
                    $s01_li2 = "마이페이지";
                    $sub_top_li1 = "<li class='active'><a href='/sub/my_lotto.php'>나의 로또</a></li>";
                    $sub_top_li2 = "";
                    $sub_top_li3 = "";
                    $sub_tit = "나의 로또";
                    $sub_top_bg = "sub_top_bg3";
            break;
		case "my_info.php":
			$s01_li2 = "마이페이지";
			$sub_top_li1 = "<li class='active'><a>회원정보</a></li>";
			$sub_tit = "회원정보 수정";
			$sub_top_bg = "sub_top_bg3";
		break;

		case "prize.php":
			$inner_x = "inner_x";
		break;

		case "deluxe.php":
			$inner_x = "inner_x";
		break;

	}

	switch($bo_table){

	case "notice":
		$s01_li2 = "고객지원";
		$sub_top_li1 = "<li class='active'><a href='/bbs/board.php?bo_table=notice'>공지사항</a></li>";
		$sub_top_li2 = "<li><a href='/bbs/faq.php?fm_id=1'>자주묻는 질문</a></li>";
		$sub_top_li3 = "<li><a href='/bbs/qalist.php'>1:1 상담</a></li>";
		$sub_tit = "공지사항";
		$sub_top_bg = "sub_top_bg1 lg-support-hero";
	break;

	case "faq":
		$s01_li2 = "고객지원";
		$sub_top_li1 = "<li><a href='/bbs/board.php?bo_table=notice'>공지사항</a></li>";
		$sub_top_li2 = "<li class='active'><a href='/bbs/faq.php?fm_id=1'>자주묻는 질문</a></li>";
		$sub_top_li3 = "<li><a href='/bbs/qalist.php'>1:1 상담</a></li>";
		$sub_tit = "자주묻는 질문";
		$sub_top_bg = "sub_top_bg1 lg-support-hero";
	break;

	}

	switch($co_id){

	case "privacy":
		$s01_li2 = "개인정보처리방침";
		$sub_top_li1 = "<li class='active'><a href='/bbs/content.php?co_id=privacy'>개인정보처리방침</a></li>";
		$sub_top_li2 = "<li><a href='/bbs/content.php?co_id=provision'>이용약관</a></li>";
		$sub_tit = "개인정보처리방침";
		$sub_top_bg = "sub_top_bg1";
	break;

	case "provision":
		$s01_li2 = "이용약관";
		$sub_top_li1 = "<li><a href='/bbs/content.php?co_id=privacy'>개인정보처리방침</a></li>";
		$sub_top_li2 = "<li class='active'><a href='/bbs/content.php?co_id=provision'>이용약관</a></li>";
		$sub_tit = "이용약관";
		$sub_top_bg = "sub_top_bg1";
	break;

	}

?>
