<?
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

		case "qalist.php": case "qawrite.php": case "qaview.php":
			$s01_li2 = "고객센터";
			$sub_top_li1 = "<li><a href='/bbs/board.php?bo_table=notice_'>공지사항</a></li>";
			$sub_top_li2 = "<li><a href='/bbs/board.php?bo_table=faq'>자주묻는 질문</a></li>";
			$sub_top_li3 = "<li class='active'><a href='/bbs/qalist.php'>1:1 상담</a></li>";
			$sub_tit = "1:1상담";
			$sub_top_bg = "sub_top_bg1";
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
			$sub_top_li3 = "<li><a href='/sub/stats3.php'>로또 구입 잘하는법</a></li>";
			$sub_tit = "로또 분석용어";
			$sub_top_bg = "sub_top_bg4";
		break;

		case "stats2.php":
			$s01_li2 = "통계분석실";
			$sub_top_li1 = "<li><a href='/sub/stats.php'>로또 분석용어</a></li>";
			$sub_top_li2 = "<li class='active'><a href='/sub/stats2.php'>확률과 조합 분석</a></li>";
			$sub_top_li3 = "<li><a href='/sub/stats3.php'>로또 구입 잘하는법</a></li>";
			$sub_tit = "확률과 조합 분석";
			$sub_top_bg = "sub_top_bg4";
		break;

		case "stats3.php":
			$s01_li2 = "통계분석실";
			$sub_top_li1 = "<li><a href='/sub/stats.php'>로또 분석용어</a></li>";
			$sub_top_li2 = "<li><a href='/sub/stats2.php'>확률과 조합 분석</a></li>";
			$sub_top_li3 = "<li class='active'><a href='/sub/stats3.php'>로또 구입 잘하는법</a></li>";
			$sub_tit = "로또 구입 잘하는법";
			$sub_top_bg = "sub_top_bg4";
		break;

		case "my_lotto.php":
			$s01_li2 = "마이페이지";
			$sub_top_li1 = "<li class='sub_top_li sub_top_li1 active'><a onClick='tabBtn(\"1\")'>나의 당첨현황</a></li>";
			$sub_top_li2 = "<li class='sub_top_li sub_top_li2'><a onClick='tabBtn(\"2\")'>나의 로또 당첨 현황</a></li>";
			$sub_top_li3 = "<li class='sub_top_li sub_top_li3'><a onClick='tabBtn(\"3\")'>나의 로또 보관함</a></li>";
			$sub_tit = "나의 당첨현황";
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

	case "notice_":
		$s01_li2 = "고객센터";
		$sub_top_li1 = "<li class='active'><a href='/bbs/board.php?bo_table=notice_'>공지사항</a></li>";
		$sub_top_li2 = "<li><a href='/bbs/board.php?bo_table=faq'>자주묻는 질문</a></li>";
		$sub_top_li3 = "<li><a href='/bbs/qalist.php'>1:1 상담</a></li>";
		$sub_tit = "공지사항";
		$sub_top_bg = "sub_top_bg1";
	break;

	case "faq":
		$s01_li2 = "고객센터";
		$sub_top_li1 = "<li><a href='/bbs/board.php?bo_table=notice_'>공지사항</a></li>";
		$sub_top_li2 = "<li class='active'><a href='/bbs/board.php?bo_table=faq'>자주묻는 질문</a></li>";
		$sub_top_li3 = "<li><a href='/bbs/qalist.php'>1:1 상담</a></li>";
		$sub_tit = "자주묻는 질문";
		$sub_top_bg = "sub_top_bg1";
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