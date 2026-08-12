# Database Documentation

이 폴더는 Lotto Platform의 데이터베이스 구조와 배포용 SQL을 관리합니다.

## 기본 원칙

- Codespaces에서는 `lotto_dev` 개발용 DB를 사용합니다.
- Cafe24 운영 DB는 사이트 개발과 테스트가 끝난 뒤 연결합니다.
- 개발 DB와 운영 DB의 접속 정보는 분리합니다.
- 운영 DB 접속 정보와 비밀번호는 GitHub에 저장하지 않습니다.
- 화면 실행 중 `ALTER TABLE`을 수행하지 않고 배포용 SQL로 분리합니다.

## 예정 파일

- `development_schema.sql`
  - Codespaces 개발용 테이블 구조
- `cafe24_migration.sql`
  - Cafe24 배포 시 한 번만 실행할 변경 SQL
- `TABLE_REFERENCE.md`
  - 사용자 정의 테이블과 컬럼 설명

## 현재 확인된 사용자 정의 테이블

- `g5_member_etc`
- `l_menu`
- `l_filter_temp`
- `l_pay`
- `l_res`
- `l_turn_temp`
- `l_turn_{회차}`

현재는 기존 코드에서 구조를 분석하고 있으며, 확인되지 않은 컬럼은 임의로 추가하지 않습니다.

## Lotto Platform 마이그레이션

- `008_lotto_filter_setting.sql`
  - 로또 필터 설정 테이블과 필터결과 관리자 메뉴
- `009_lotto_filter_final_defaults.sql`
  - 최종 로또 필터 기본값
- `010_lotto_result_admin_menu.sql`
  - 필터결과 메뉴를 관리자 이상으로 제한
  - 당첨결과 관리자 메뉴 추가 및 직원/팀장/관리자 접근 설정
