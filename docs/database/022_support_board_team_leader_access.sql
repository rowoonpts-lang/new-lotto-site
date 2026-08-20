/*
 * 고객지원 게시판 관리 권한
 *
 * 허용 권한:
 * - 팀장 7
 * - 관리자 9
 * - 최고관리자 10
 */

UPDATE l_menu
SET
    lm_level = '|7|9|10|',
    lm_use = 1
WHERE lm_cate1 = 300
  AND lm_cate2 IN (0, 10, 20, 30);
