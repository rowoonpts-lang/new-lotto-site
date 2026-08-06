# Lotto Platform 우선 점검 목록

> Cafe24 가입 전 홈페이지와 관리자페이지를 완성하기 위한 집중 점검 문서입니다.

## 1. 추적 중인 PHP 파일 영역별 수

- `theme`: 394개
- `lib`: 258개
- `adm`: 255개
- `shop`: 191개
- `mobile`: 187개
- `lpadm`: 147개
- `lpadmin`: 122개
- `bbs`: 106개
- `skin`: 101개
- `sub`: 60개
- `auto`: 23개
- `landing`: 16개
- `(root)`: 13개
- `event`: 10개
- `extend`: 10개
- `ad`: 9개
- `install`: 9개
- `ajax`: 3개
- `proc`: 3개

## 2. 우선 점검 영역

### (root) (13개)

- `_common.php`
- `_head.php`
- `_tail.php`
- `cloudflare.check.php`
- `common.php`
- `config.php`
- `head.php`
- `head.sub.php`
- `index.php`
- `shop.config.php`
- `tail.php`
- `tail.sub.php`
- `version.php`

### ad (9개)

- `ad/_common.php`
- `ad/doc.v1.php`
- `ad/json_sample_php.php`
- `ad/process.excel.php`
- `ad/process.list.php`
- `ad/process.login.check.php`
- `ad/process.login.php`
- `ad/process.logout.php`
- `ad/process.php`

### ajax (3개)

- `ajax/_common.php`
- `ajax/ajax.find.mb_hp.php`
- `ajax/ajax.find.mb_id.php`

### auto (23개)

- `auto/_common.php`
- `auto/ajax.process01.switch.php`
- `auto/ajax.timecheck01.php`
- `auto/ajax.timecheck02.php`
- `auto/ajax.timecheck03.php`
- `auto/ajax.timecheck04.php`
- `auto/ajax.timecheck05.php`
- `auto/ajax.timecheck06.php`
- `auto/auto.php`
- `auto/debug_sms_v2.php`
- `auto/manual_mass_send.php`
- `auto/manual_mass_send_ajax.php`
- `auto/manual_send_wed.php`
- `auto/one_create.list.php`
- `auto/one_create.proc.php`
- `auto/one_update.proc.php`
- `auto/process.01.php`
- `auto/process.02.php`
- `auto/process.03.php`
- `auto/process.04.php`
- `auto/process.04__.php`
- `auto/process.05.php`
- `auto/process.06.php`

### event (10개)

- `event/event1/_common.php`
- `event/event1/index.php`
- `event/event1/m.php`
- `event/event1/order_update.php`
- `event/event1/pc.php`
- `event/event2/_common.php`
- `event/event2/index.php`
- `event/event2/m.php`
- `event/event2/order_update.php`
- `event/event2/pc.php`

### extend (10개)

- `extend/debugbar.extend.php`
- `extend/default.config.php`
- `extend/g5_54version_update.extend.php`
- `extend/lotto.platform.extend.php`
- `extend/shop.extend.php`
- `extend/smarteditor_upload_extend.php`
- `extend/sms5.extend.php`
- `extend/social_login.extend.php`
- `extend/user.config.php`
- `extend/version.extend.php`

### landing (16개)

- `landing/2ns1/_common.php`
- `landing/2ns1/index.php`
- `landing/2ns1/order_update.php`
- `landing/2rl1/_common.php`
- `landing/2rl1/index.php`
- `landing/2rl1/order_update.php`
- `landing/2robot1/_common.php`
- `landing/2robot1/index.php`
- `landing/2robot1/order_update.php`
- `landing/cre/_common.php`
- `landing/cre/index.php`
- `landing/cre/order_update.php`
- `landing/ns1/_common.php`
- `landing/ns1/index.200831.php`
- `landing/ns1/index.php`
- `landing/ns1/order_update.php`

### lpadm (147개)

- `lpadm/_common.php`
- `lpadm/admin.head.php`
- `lpadm/admin.lib.php`
- `lpadm/admin.menu100.php`
- `lpadm/admin.menu200.php`
- `lpadm/admin.menu300.php`
- `lpadm/admin.menu900.php`
- `lpadm/admin.tail.php`
- `lpadm/ajax.token.php`
- `lpadm/ajax.use_captcha.php`
- `lpadm/auth_list.php`
- `lpadm/auth_list_delete.php`
- `lpadm/auth_update.php`
- `lpadm/board_copy.php`
- `lpadm/board_copy_update.php`
- `lpadm/board_delete.inc.php`
- `lpadm/board_form.php`
- `lpadm/board_form_update.php`
- `lpadm/board_list.php`
- `lpadm/board_list_update.php`
- `lpadm/board_thumbnail_delete.php`
- `lpadm/boardgroup_form.php`
- `lpadm/boardgroup_form_update.php`
- `lpadm/boardgroup_list.php`
- `lpadm/boardgroup_list_update.php`
- `lpadm/boardgroupmember_form.php`
- `lpadm/boardgroupmember_list.php`
- `lpadm/boardgroupmember_update.php`
- `lpadm/browscap.php`
- `lpadm/browscap_convert.php`
- `lpadm/browscap_converter.php`
- `lpadm/browscap_update.php`
- `lpadm/cache_file_delete.php`
- `lpadm/captcha_file_delete.php`
- `lpadm/config_form.php`
- `lpadm/config_form_update.php`
- `lpadm/contentform.php`
- `lpadm/contentformupdate.php`
- `lpadm/contentlist.php`
- `lpadm/dbupgrade.php`
- `lpadm/faqform.php`
- `lpadm/faqformupdate.php`
- `lpadm/faqlist.php`
- `lpadm/faqmasterform.php`
- `lpadm/faqmasterformupdate.php`
- `lpadm/faqmasterlist.php`
- `lpadm/index.php`
- `lpadm/mail_delete.php`
- `lpadm/mail_form.php`
- `lpadm/mail_list.php`
- `lpadm/mail_preview.php`
- `lpadm/mail_select_form.php`
- `lpadm/mail_select_list.php`
- `lpadm/mail_select_update.php`
- `lpadm/mail_test.php`
- `lpadm/mail_update.php`
- `lpadm/member_delete.php`
- `lpadm/member_form.php`
- `lpadm/member_form_update.php`
- `lpadm/member_list.php`
- `lpadm/member_list_delete.php`
- `lpadm/member_list_update.php`
- `lpadm/menu_form.php`
- `lpadm/menu_form_search.php`
- `lpadm/menu_list.php`
- `lpadm/menu_list_update.php`
- `lpadm/newwinform.php`
- `lpadm/newwinformupdate.php`
- `lpadm/newwinlist.php`
- `lpadm/phpinfo.php`
- `lpadm/point_list.php`
- `lpadm/point_list_delete.php`
- `lpadm/point_update.php`
- `lpadm/poll_delete.php`
- `lpadm/poll_form.php`
- `lpadm/poll_form_update.php`
- `lpadm/poll_list.php`
- `lpadm/popular_list.php`
- `lpadm/popular_rank.php`
- `lpadm/qa_config.php`
- `lpadm/qa_config_update.php`
- `lpadm/safe_check.php`
- `lpadm/sendmail_test.php`
- `lpadm/service.php`
- `lpadm/session_file_delete.php`
- `lpadm/sms_admin/_common.php`
- `lpadm/sms_admin/ajax.hp_chk.php`
- `lpadm/sms_admin/ajax.sms_write_form.php`
- `lpadm/sms_admin/ajax.sms_write_group.php`
- `lpadm/sms_admin/ajax.sms_write_level.php`
- `lpadm/sms_admin/ajax.sms_write_person.php`
- `lpadm/sms_admin/config.php`
- `lpadm/sms_admin/config_update.php`
- `lpadm/sms_admin/emoticon_move.php`
- `lpadm/sms_admin/emoticon_move_update.php`
- `lpadm/sms_admin/form_group.php`
- `lpadm/sms_admin/form_group_move.php`
- `lpadm/sms_admin/form_group_update.php`
- `lpadm/sms_admin/form_list.php`
- `lpadm/sms_admin/form_multi_update.php`
- `lpadm/sms_admin/form_update.php`
- `lpadm/sms_admin/form_write.php`
- `lpadm/sms_admin/history_list.php`
- `lpadm/sms_admin/history_num.php`
- `lpadm/sms_admin/history_send.php`
- `lpadm/sms_admin/history_view.php`
- `lpadm/sms_admin/install.php`
- `lpadm/sms_admin/member_update.php`
- `lpadm/sms_admin/member_update_run.php`
- `lpadm/sms_admin/num_book.php`
- `lpadm/sms_admin/num_book_file.php`
- `lpadm/sms_admin/num_book_file_download.php`
- `lpadm/sms_admin/num_book_file_upload.php`
- `lpadm/sms_admin/num_book_move.php`
- `lpadm/sms_admin/num_book_multi_update.php`
- `lpadm/sms_admin/num_book_update.php`
- `lpadm/sms_admin/num_book_write.php`
- `lpadm/sms_admin/num_group.php`
- `lpadm/sms_admin/num_group_move.php`
- `lpadm/sms_admin/num_group_update.php`
- `lpadm/sms_admin/number_move_update.php`
- `lpadm/sms_admin/sms_ing.php`
- `lpadm/sms_admin/sms_write.php`
- `lpadm/sms_admin/sms_write_form.php`
- `lpadm/sms_admin/sms_write_overlap_check.php`
- `lpadm/sms_admin/sms_write_send.php`
- `lpadm/theme.php`
- `lpadm/theme_config_load.php`
- `lpadm/theme_detail.php`
- `lpadm/theme_preview.php`
- `lpadm/theme_update.php`
- `lpadm/thumbnail_file_delete.php`
- `lpadm/visit.sub.php`
- `lpadm/visit_browser.php`
- `lpadm/visit_date.php`
- `lpadm/visit_delete.php`
- `lpadm/visit_delete_update.php`
- `lpadm/visit_device.php`
- `lpadm/visit_domain.php`
- `lpadm/visit_hour.php`
- `lpadm/visit_list.php`
- `lpadm/visit_month.php`
- `lpadm/visit_os.php`
- `lpadm/visit_search.php`
- `lpadm/visit_week.php`
- `lpadm/visit_year.php`
- `lpadm/write_count.php`

### lpadmin (122개)

- `lpadmin/_common.php`
- `lpadmin/ad/_common.php`
- `lpadmin/ad/ad.data.list.del.php`
- `lpadmin/ad/ad.data.list.excel.php`
- `lpadmin/ad/ad.data.list.php`
- `lpadmin/ad/ad.list.php`
- `lpadmin/ad/ajax.find.code.php`
- `lpadmin/ad/ajax.find.id.php`
- `lpadmin/ad/pop.new_ad.del.php`
- `lpadmin/ad/pop.new_ad.php`
- `lpadmin/ad/pop.new_ad.update.php`
- `lpadmin/bbs/_common.php`
- `lpadmin/bbs/bbs.res.action.del.php`
- `lpadmin/bbs/bbs.res.action.excel.php`
- `lpadmin/bbs/bbs.res.action.php`
- `lpadmin/bbs/bbs.res.db.del.php`
- `lpadmin/bbs/bbs.res.db.excel.php`
- `lpadmin/bbs/bbs.res.db.php`
- `lpadmin/bbs/bbs.res.list.del.php`
- `lpadmin/bbs/bbs.res.list.excel.php`
- `lpadmin/bbs/bbs.res.list.php`
- `lpadmin/check_sms_log_v3.php`
- `lpadmin/del.process.php`
- `lpadmin/emp/_common.php`
- `lpadmin/emp/ajax.saveIp.php`
- `lpadmin/emp/emp.add.php`
- `lpadmin/emp/emp.memo.php`
- `lpadmin/emp/emp.save.php`
- `lpadmin/emp/pop.emp.new_member.php`
- `lpadmin/head.php`
- `lpadmin/head.sub.php`
- `lpadmin/index.php`
- `lpadmin/login.check.php`
- `lpadmin/login.php`
- `lpadmin/login.step2.check.php`
- `lpadmin/login.step2.php`
- `lpadmin/logout.php`
- `lpadmin/lucky/_common.php`
- `lpadmin/lucky/filter.php`
- `lpadmin/lucky/filter.update.php`
- `lpadmin/lucky/lucky.custom.php`
- `lpadmin/lucky/lucky.custom.save.php`
- `lpadmin/lucky/lucky.list.excel.php`
- `lpadmin/lucky/lucky.list.php`
- `lpadmin/lucky/lucky.view.php`
- `lpadmin/lucky/lucky.view.update.php`
- `lpadmin/lucky/make.3th.php`
- `lpadmin/lucky/make.4th.php`
- `lpadmin/lucky/test.del.php`
- `lpadmin/lucky/test.php`
- `lpadmin/member/_common.php`
- `lpadmin/member/ajax.calc.leftday.php`
- `lpadmin/member/ajax.checkAlarm.php`
- `lpadmin/member/ajax.create.number.php`
- `lpadmin/member/ajax.find.db.php`
- `lpadmin/member/ajax.getPrice.php`
- `lpadmin/member/ajax.mu.save.php`
- `lpadmin/member/ajax.pop.memo.hk.php`
- `lpadmin/member/ajax.proc.view.php`
- `lpadmin/member/ajax.sms.id.send.php`
- `lpadmin/member/ajax.sms.id.send2.php`
- `lpadmin/member/ajax.smsReSend.php`
- `lpadmin/member/alarm.list.php`
- `lpadmin/member/inc.alarmList.php`
- `lpadmin/member/inc.calendar.php`
- `lpadmin/member/member.all.excel.php`
- `lpadmin/member/member.all.php`
- `lpadmin/member/member.alldel.php`
- `lpadmin/member/member.del.php`
- `lpadmin/member/member.head.php`
- `lpadmin/member/member.save.php`
- `lpadmin/member/member.search.php`
- `lpadmin/member/payment.cancel.php`
- `lpadmin/member/payment.mu.pay.php`
- `lpadmin/member/pop.member_info.php`
- `lpadmin/member/pop.member_info.stop.php`
- `lpadmin/member/pop.member_info.update.php`
- `lpadmin/member/pop.memo.php`
- `lpadmin/member/pop.memo.update.php`
- `lpadmin/member/pop.new_member.php`
- `lpadmin/member/pop.payment.cancel.php`
- `lpadmin/member/pop.payment.in.cancel.php`
- `lpadmin/member/pop.payment.in.update.php`
- `lpadmin/member/pop.payment.php`
- `lpadmin/member/pop.payment.update.php`
- `lpadmin/member/pop.sms.php`
- `lpadmin/member/pop.success.php`
- `lpadmin/member/pop.success.update.php`
- `lpadmin/member/proc.mbin.php`
- `lpadmin/member/sms.udpate.php`
- `lpadmin/payment/_common.php`
- `lpadmin/payment/in.excel.php`
- `lpadmin/payment/in.php`
- `lpadmin/payment/payment.all.excel.php`
- `lpadmin/payment/payment.all.php`
- `lpadmin/payment/payment.cancel.php`
- `lpadmin/payment/payment.credit.pay.php`
- `lpadmin/payment/payment.credit.php`
- `lpadmin/payment/payment.mu.befor.php`
- `lpadmin/payment/payment.mu.before.excel.php`
- `lpadmin/payment/payment.mu.excel.php`
- `lpadmin/payment/payment.mu.php`
- `lpadmin/payment/payment.php`
- `lpadmin/payment/payment2.php`
- `lpadmin/payment/sample.php`
- `lpadmin/payment/sample2.php`
- `lpadmin/program/_common.php`
- `lpadmin/program/lotto.number.php`
- `lpadmin/readme.php`
- `lpadmin/sms/_common.php`
- `lpadmin/sms/sms.excel.number.upload.php`
- `lpadmin/sms/sms.excel.number.upload.update.php`
- `lpadmin/sms/sms.excel1.upload.php`
- `lpadmin/sms/sms.excel1.upload.update.php`
- `lpadmin/sms/sms.excel2.upload.php`
- `lpadmin/sms/sms.list.php`
- `lpadmin/sms/sms.spam.del.php`
- `lpadmin/sms/sms.spam.php`
- `lpadmin/statistics/_common.php`
- `lpadmin/statistics/st.list.php`
- `lpadmin/table_stadard.php`
- `lpadmin/tail.php`

### sub (60개)

- `sub/_common.php`
- `sub/about.php`
- `sub/acrylic.php`
- `sub/ajax.find.id.php`
- `sub/ajax.fnOpenPopAjax.php`
- `sub/ajax.hp.check.php`
- `sub/ajax.mob.turn.list.view.php`
- `sub/ajax.my_lotto1.php`
- `sub/ajax.my_lotto2.php`
- `sub/ajax.res.php`
- `sub/ajax.res2.php`
- `sub/ajax.smsMu.php`
- `sub/ajax.submit.php`
- `sub/ajax.submit2.php`
- `sub/ajax.turn.list.view.php`
- `sub/ajax.turn.list.view2.php`
- `sub/ci.php`
- `sub/cs.php`
- `sub/data01.php`
- `sub/data02.php`
- `sub/data03.php`
- `sub/deluxe.php`
- `sub/detail.php`
- `sub/head.tit.php`
- `sub/head.tit_.php`
- `sub/instagram.php`
- `sub/main.lucky.php`
- `sub/map.php`
- `sub/membership.php`
- `sub/my_info.php`
- `sub/my_info.update.php`
- `sub/my_lotto.php`
- `sub/my_lotto02.php`
- `sub/my_lotto03.php`
- `sub/notmessage.php`
- `sub/page.check.php`
- `sub/perfect_member.php`
- `sub/perfect_member_.php`
- `sub/perfect_member__.php`
- `sub/post.php`
- `sub/prize.php`
- `sub/res.php`
- `sub/sms.php`
- `sub/stats.php`
- `sub/stats2.php`
- `sub/stats3.php`
- `sub/sub0101.php`
- `sub/sub0101_.php`
- `sub/sub0102_.php`
- `sub/sub0201_.php`
- `sub/sub0301.php`
- `sub/sub0301_.php`
- `sub/sub0302.php`
- `sub/sub0303.php`
- `sub/sub0501.php`
- `sub/sub_tab.php`
- `sub/system.php`
- `sub/test.php`
- `sub/test2.php`
- `sub/want_thumnail.php`

## 3. 우선 영역의 페이지 연결

### ad

- `process.login.check.php`

### event

- `./order_update.php`
- `http://www.lottoclick.co.kr/sub/sub0201.php`

### landing

- `./order_update.php`

### lpadm

- `./auth_list_delete.php`
- `./auth_update.php`
- `./board_copy.php?bo_table=`
- `./board_copy_update.php`
- `./board_form.php`
- `./board_form.php?gr_id=`
- `./board_form.php?w=u&amp;bo_table=`
- `./board_form_update.php`
- `./board_list.php?sfl=a.gr_id&stx=`
- `./board_list_update.php`
- `./boardgroup_form.php`
- `./boardgroup_form.php?`
- `./boardgroup_form_update.php`
- `./boardgroup_list_update.php`
- `./boardgroupmember_form.php?mb_id=`
- `./boardgroupmember_list.php?gr_id=`
- `./boardgroupmember_update.php`
- `./config_form_update.php`
- `./config_update.php`
- `./contentform.php`
- `./contentformupdate.php`
- `./contentlist.php`
- `./emoticon_move.php`
- `./emoticon_move_update.php`
- `./faqformupdate.php`
- `./faqmasterform.php`
- `./faqmasterformupdate.php`
- `./faqmasterlist.php`
- `./form_group_update.php`
- `./form_list.php?fg_no=0`
- `./form_list.php?page=`
- `./form_multi_update.php`
- `./form_multi_update.php?w=`
- `./form_update.php?w=d&fo_no=`
- `./history_send.php`
- `./mail_delete.php`
- `./mail_form.php`
- `./mail_list.php`
- `./mail_preview.php?ma_id=`
- `./mail_select_list.php`
- `./mail_select_update.php`
- `./mail_update.php`
- `./member_delete.php?`
- `./member_form.php`
- `./member_form.php?`
- `./member_form.php?$qstr&amp;w=u&amp;mb_id=`
- `./member_form_update.php`
- `./member_list.php`
- `./member_list_update.php`
- `./member_update_run.php`
- `./menu_list_update.php`
- `./newwinform.php`
- `./newwinformupdate.php`
- `./newwinlist.php`
- `./num_book.php?bg_no=1`
- `./num_book.php?page=`
- `./num_book_file_download.php`
- `./num_book_move.php`
- `./num_book_multi_update.php`
- `./num_book_update.php`
- `./num_group_update.php`
- `./number_move_update.php`
- `./point_list.php`
- `./point_list_delete.php`
- `./point_update.php`
- `./poll_delete.php`
- `./poll_form.php`
- `./poll_form.php?`
- `./poll_form_update.php`
- `./qa_config_update.php`
- `./visit_delete_update.php`
- `./visit_list.php?`
- `config.php`
- `form_group_move.php?fg_no=`
- `form_group_update.php?w=`
- `form_update.php`
- `http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2`
- `http://sir.kr/main/service/b_cert.php`
- `http://sir.kr/main/service/b_ipin.php`
- `http://sir.kr/main/service/lg_cert.php`
- `http://sir.kr/main/service/p_cert.php`
- `num_book_file_upload.php`
- `num_book_file_upload.php?confirm=1`
- `num_group_move.php?bg_no=`
- `num_group_update.php?mw=d&bg_no=`
- `num_group_update.php?mw=empty&bg_no=`
- `sms_write_send.php`

### lpadmin

- `./ad.data.list.del.php?idx=`
- `./bbs.res.db.del.php?idx=`
- `./bbs.res.list.del.php?lr_id=`
- `./member.del.php?mb_id=`
- `./payment.cancel.php?lp_id=`
- `./payment.mu.pay.php?lp_id=`
- `./pop.member_info.stop.php?mb_id=`
- `./pop.new_ad.del.php?idx=`
- `./proc.mbin.php?type=`
- `./sms.excel.number.upload.update.php`
- `./sms.excel1.upload.update.php`
- `./sms.spam.del.php?pn=`
- `emp.save.php`
- `login.check.php`
- `login.step2.check.php`
- `lucky.custom.save.php`
- `lucky.view.update.php`
- `member.save.php`
- `pop.member_info.update.php`
- `pop.memo.update.php`
- `pop.new_ad.update.php`
- `pop.payment.cancel.php?lp_id=`
- `pop.payment.in.cancel.php?lp_id=`
- `pop.payment.in.update.php`
- `pop.payment.update.php`
- `sms.udpate.php`

### sub

- `/bbs/board.php?bo_table=faq`
- `/bbs/board.php?bo_table=notice_`
- `/bbs/content.php?co_id=privacy`
- `/bbs/content.php?co_id=provision`
- `/bbs/login.php`
- `/bbs/qalist.php`
- `/bbs/register.php`
- `/sub/stats.php`
- `/sub/stats2.php`
- `/sub/stats3.php`

## 4. 폼 전송 대상

### (root)

- `<?php echo G5_BBS_URL ?>/search.php`

### ad

- `process.login.check.php`

### event

- `./order_update.php`

### landing

- `./order_update.php`
- `<?php echo G5_BBS_URL ?>/order_update.php`

### lpadm

- `./auth_list_delete.php`
- `./auth_update.php`
- `./board_copy_update.php`
- `./board_form_update.php`
- `./board_list_update.php`
- `./boardgroup_form_update.php`
- `./boardgroup_list_update.php`
- `./boardgroupmember_update.php`
- `./config_update.php`
- `./contentformupdate.php`
- `./emoticon_move_update.php`
- `./faqformupdate.php`
- `./faqmasterformupdate.php`
- `./form_multi_update.php`
- `./history_send.php`
- `./mail_delete.php`
- `./mail_select_list.php`
- `./mail_select_update.php`
- `./mail_update.php`
- `./member_form_update.php`
- `./member_list_update.php`
- `./member_update_run.php`
- `./menu_list_update.php`
- `./newwinformupdate.php`
- `./num_book_multi_update.php`
- `./num_book_update.php`
- `./num_group_update.php`
- `./number_move_update.php`
- `./point_list_delete.php`
- `./point_update.php`
- `./poll_delete.php`
- `./poll_form_update.php`
- `./visit_delete_update.php`
- `<?php echo $_SERVER[`
- `form_update.php`
- `sms_write_send.php`

### lpadmin

- `./sms.excel.number.upload.update.php`
- `./sms.excel1.upload.update.php`
- `emp.save.php`
- `login.check.php`
- `login.step2.check.php`
- `lucky.custom.save.php`
- `lucky.view.update.php`
- `member.save.php`
- `pop.member_info.update.php`
- `pop.memo.update.php`
- `pop.new_ad.update.php`
- `sms.udpate.php`

### sub

- `(현재 페이지)`
- `<?=G5_URL?>/sub/my_info.update.php`

## 5. 공통 파일 호출

### (root)

- `./_common.php`
- `./common.php`
- `cloudflare.check.php`

### ad

- `../common.php`
- `./_common.php`
- `_common.php`

### ajax

- `../common.php`
- `_common.php`

### auto

- `_common.php`

### event

- `../../common.php`
- `./_common.php`
- `./m.php`
- `./pc.php`
- `_common.php`

### landing

- `../../common.php`
- `_common.php`

### lpadm

- `../../common.php`
- `../common.php`
- `./_common.php`
- `./admin.head.php`
- `./admin.tail.php`
- `./board_delete.inc.php`
- `./safe_check.php`
- `./sms_write_form.php`
- `./visit.sub.php`

### lpadmin

- `../../common.php`
- `../common.php`
- `./_common.php`
- `./inc.alarmList.php`
- `./member.head.php`
- `_common.php`

### sub

- `../common.php`
- `./_common.php`
- `_common.php`

## 6. PHP 8 위험 후보

### 따옴표 없는 배열 키: 327건

- `ajax/ajax.find.mb_id.php:7` — `echo $row[cnt];`
- `auto/ajax.timecheck01.php:8` — `$weekAry = explode("|",$config[cf_auto1_week]);`
- `auto/ajax.timecheck01.php:15` — `if($row[cf_auto1_date] != date("Y-m-d")){`
- `auto/ajax.timecheck01.php:18` — `if($row[cf_auto1_time] < date("H:i:s")){`
- `auto/ajax.timecheck01.php:22` — `if($row[cf_auto1_time] < date("H:i:s")){`
- `auto/ajax.timecheck01.php:23` — `if($row[cf_auto1_ing] != 2){`
- `auto/ajax.timecheck02.php:8` — `$weekAry = explode("|",$config[cf_auto2_week]);`
- `auto/ajax.timecheck02.php:15` — `if($row[cf_auto2_date] != date("Y-m-d")){`
- `auto/ajax.timecheck02.php:18` — `if($row[cf_auto2_time] < date("H:i:s")){`
- `auto/ajax.timecheck02.php:22` — `if($row[cf_auto2_time] < date("H:i:s")){`
- `auto/ajax.timecheck02.php:23` — `if($row[cf_auto2_ing] != 2){`
- `auto/ajax.timecheck03.php:8` — `$weekAry = explode("|",$config[cf_auto3_week]);`
- `auto/ajax.timecheck03.php:15` — `if($row[cf_auto3_date] != date("Y-m-d")){`
- `auto/ajax.timecheck03.php:18` — `if($row[cf_auto3_time] < date("H:i:s")){`
- `auto/ajax.timecheck03.php:22` — `if($row[cf_auto3_time] < date("H:i:s")){`
- `auto/ajax.timecheck03.php:23` — `if($row[cf_auto3_ing] != 2){`
- `auto/ajax.timecheck04.php:8` — `$weekAry = explode("|",$config[cf_auto4_week]);`
- `auto/ajax.timecheck04.php:15` — `if($row[cf_auto4_date] != date("Y-m-d")){`
- `auto/ajax.timecheck04.php:18` — `if($row[cf_auto4_time] < date("H:i:s")){`
- `auto/ajax.timecheck04.php:22` — `if($row[cf_auto4_time] < date("H:i:s")){`
- `auto/ajax.timecheck04.php:23` — `if($row[cf_auto4_ing] != 2){`
- `auto/ajax.timecheck05.php:8` — `$weekAry = explode("|",$config[cf_auto5_week]);`
- `auto/ajax.timecheck05.php:15` — `if($row[cf_auto5_date] != date("Y-m-d")){`
- `auto/ajax.timecheck05.php:18` — `if($row[cf_auto5_time] < date("H:i:s")){`
- `auto/ajax.timecheck05.php:22` — `if($row[cf_auto5_time] < date("H:i:s")){`
- `auto/ajax.timecheck05.php:23` — `if($row[cf_auto5_ing] != 2){`
- `auto/ajax.timecheck06.php:15` — `if($row[cf_auto6_date] != date("Y-m-d")){`
- `auto/ajax.timecheck06.php:18` — `if($row[cf_auto6_time] < date("H:i:s")){`
- `auto/ajax.timecheck06.php:22` — `if($row[cf_auto6_time] < date("H:i:s")){`
- `auto/ajax.timecheck06.php:23` — `if($row[cf_auto6_ing] != 2){`
- `auto/auto.php:6` — `if($config[cf_auto1_date] != date("Y-m-d")){`
- `auto/auto.php:10` — `/*if($config[cf_auto1_ing] != "2"){`
- `lpadm/config_form_update.php:239` — `//sql_query(" OPTIMIZE TABLE `$g5[config_table]` ");`
- `lpadm/sms_admin/history_view.php:155` — `<!-- <td><?php echo $res[wr_message]; ?></span></td>-->`
- `lpadm/sms_admin/history_view.php:156` — `<!-- <td><?php echo $res[wr_reply]; ?></td>-->`
- `lpadm/sms_admin/history_view.php:163` — `<!-- <a href="./history_del.php?page=<?php echo $page?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>&amp;wr_no=<?php echo $res[wr_no]?>&amp;wr_renum=<?php ec`
- `lpadm/sms_admin/num_book_update.php:114` — `$res = sql_fetch("select * from $g5[sms5_book_table] where bk_no='$bk_no'");`
- `lpadm/sms_admin/num_book_update.php:118` — `if (!$res[mb_id])`
- `lpadm/sms_admin/num_book_update.php:120` — `if ($res[receipt] == 1)`
- `lpadm/sms_admin/num_book_update.php:125` — `sql_query("delete from $g5[sms5_book_table] where bk_no='$bk_no'");`
- `lpadm/sms_admin/num_book_update.php:126` — `sql_query("update $g5[sms5_book_group_table] set bg_count = bg_count - 1, bg_nomember = bg_nomember - 1, $sql_sms where bg_no = '$res[bg_no]'");`
- `lpadm/sms_admin/sms_write.php:526` — `//echo "add(\"$row[wr_message]\");\n";`
- `lpadmin/ad/ad.list.php:141` — `<!--td><button type="button" class="btn btn-block btn-danger" onClick="fnMemmberInfo('<?=base64_encode($row[mb_id])?>')">정보수정</button></td-->`
- `lpadmin/ad/ajax.find.code.php:7` — `echo $row[cnt];`
- `lpadmin/ad/ajax.find.id.php:7` — `echo $row[cnt];`
- `lpadmin/ad/pop.new_ad.php:42` — `<option value="<?=$row2[lu_type]?>|<?=$row2[lu_name]?>"><?=$row2[lu_name]?></option>`
- `lpadmin/ad/pop.new_ad.php:97` — `<input type="radio" id="radioPrimary1" name="st_tp" <?php if($w == ""){?>checked<?php }else{if($row[st_tp] == "1"){echo "checked";}}?> value="1">`
- `lpadmin/ad/pop.new_ad.php:103` — `<input type="radio" id="radioPrimary2" name="st_tp" <?php if($row[st_tp] == "0"){echo "checked";}?> value="0">`
- `lpadmin/bbs/bbs.res.action.php:134` — `<button type="button" class="btn btn-danger" onClick="fnProcDel('l_res','lr_id','<?=$row[lr_id]?>')">삭제</button>`
- `lpadmin/bbs/bbs.res.db.php:124` — `<button type="button" class="btn btn-danger" onClick="fnProcDel('l_ad_list_in','idx','<?=$row[idx]?>')">삭제</button>`
- `lpadmin/emp/emp.add.php:113` — `<td><button type="button" class="btn btn-block btn-primary" onclick="fnAddMemmber('<?=base64_encode($row[mb_id])?>')">수정</button></td>`
- `lpadmin/emp/emp.add.php:114` — `<td><button type="button" class="btn btn-block btn-danger" onclick="fnMemberDel('<?=base64_encode($row[mb_id])?>')">삭제</button></td>`
- `lpadmin/emp/emp.memo.php:20` — `$memoAry[substr($row[lm_datetime],0,10)][$row[from_mb_id]] = $memoAry[substr($row[lm_datetime],0,10)][$row[from_mb_id]]+1;`
- `lpadmin/emp/emp.memo.php:99` — `<td style="width:100px;"><?=$row[mb_name]?><?=$row[mb_team]?></td>`
- `lpadmin/emp/emp.memo.php:103` — `$totmem[$row[mb_id]] += $memoAry[$year."-".$month."-".$toDayTmp][$row[mb_id]];`
- `lpadmin/emp/emp.memo.php:106` — `<?=$memoAry[$year."-".$month."-".$toDayTmp][$row[mb_id]]?>`
- `lpadmin/emp/emp.memo.php:110` — `<?=$totmem[$row[mb_id]]?>`
- `lpadmin/emp/pop.emp.new_member.php:23` — `<input type="hidden" id="mb_no" name="mb_no" value="<?=$row[mb_no]?>">`
- `lpadmin/emp/pop.emp.new_member.php:31` — `<input type="text" class="form-control" id="mb_hp" name="mb_hp" placeholder="" value=<?=$row[mb_hp]?> <?php if($mb_id){?>readonly<?php }?>>`
- `lpadmin/emp/pop.emp.new_member.php:42` — `<input type="text" class="form-control" id="mb_name" name="mb_name" placeholder="" required value=<?=$row[mb_name]?>>`
- `lpadmin/emp/pop.emp.new_member.php:48` — `<input type="text" class="form-control" id="mb_id" name="mb_id" placeholder="" value="<?=$row[mb_id]?>" <?php if($mb_id){?>readonly<?php }?>>`
- `lpadmin/emp/pop.emp.new_member.php:59` — `<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="" value="<?=$row[emp_pw]?>" required>`
- `lpadmin/emp/pop.emp.new_member.php:72` — `<option value="<?=$list[$i]?>" <?php if($row[mb_team] ==$list[$i]){echo "selected";}?>><?=$list[$i]?></option>`
- `lpadmin/emp/pop.emp.new_member.php:91` — `<option value="<?=$i+5?>" <?php if($row[mb_level] == ($i+5)){echo "selected";}?>><?=$list[$i]?></option>`
- `lpadmin/lucky/lucky.custom.php:94` — `$ball_text = $row[num1].",".$row[num2].",".$row[num3].",".$row[num4].",".$row[num5].",".$row[num6];`
- `lpadmin/lucky/lucky.custom.php:100` — `<td><?=getBall($row[num1])?></td>`
- `lpadmin/lucky/lucky.custom.php:101` — `<td><?=getBall($row[num2])?></td>`
- `lpadmin/lucky/lucky.custom.php:102` — `<td><?=getBall($row[num3])?></td>`
- `lpadmin/lucky/lucky.custom.php:103` — `<td><?=getBall($row[num4])?></td>`
- `lpadmin/lucky/lucky.custom.php:104` — `<td><?=getBall($row[num5])?></td>`
- `lpadmin/lucky/lucky.custom.php:105` — `<td><?=getBall($row[num6])?></td>`
- `lpadmin/lucky/lucky.custom.php:106` — `<td><?=getBall($row[num7])?></td>`
- `lpadmin/lucky/lucky.custom.php:107` — `<td><?=$row[lc_datetime]?></td>`
- `lpadmin/lucky/lucky.custom.php:108` — `<td><button class="btn btn-block btn-danger" type="button" onClick="fnProcDel('l_lucky_custom', 'lc_id', '<?=$row[lc_id]?>')">삭제</button></td>`
- `lpadmin/lucky/lucky.list.excel.php:68` — `$ball_text = $row[num1].",".$row[num2].",".$row[num3].",".$row[num4].",".$row[num5].",".$row[num6];`
- `lpadmin/lucky/lucky.list.excel.php:80` — `<td><?=$row[lp_pay_datetime]?></td>`
- `lpadmin/lucky/lucky.list.excel.php:81` — `<td><?=$row[lt_datetime]?></td>`
- `lpadmin/member/ajax.create.number.php:11` — `if($row[recent_turn] != $newTurn){`
- `lpadmin/member/ajax.find.db.php:7` — `echo $row[lu_code];`
- `lpadmin/member/ajax.proc.view.php:10` — `fnSendOneshot($config['cf_oneshot_tel'], $row[mb_hp], $msg , '');`

## 7. 자동 분석에서 제외한 대용량 파일

- 없음

## 8. 처리 순서

1. 실제 홈페이지 메뉴와 연결된 공개 화면
2. 회원가입·로그인·마이페이지
3. 로또 신청·예약·당첨 화면
4. 결제·포인트·환급
5. lpadmin 실제 메뉴
6. 등록·수정·삭제 처리 파일
7. 모바일 화면
8. 미사용·중복·백업 파일 판정
9. 보안 검사
10. Cafe24 배포 준비

