# Development Guide

## 개발 원칙

- 기존 운영 기능을 유지한다.
- 변경 범위를 최소화한다.
- 한 번에 하나의 기능만 수정한다.
- 수정 전 반드시 현재 코드를 분석한다.
- 모든 변경 사항은 Git으로 관리한다.

## 작업 순서

1. 코드 분석
2. 문제점 분석
3. 영향 범위 확인
4. 개선 계획 수립
5. 코드 구현
6. 테스트
7. Git Commit
8. 문서 업데이트

## 브랜치 규칙

- feature/*
- fix/*
- refactor/*
- docs/*

## Commit Message 예시

- docs: add initial project documentation
- refactor: improve common library structure
- fix: resolve login session issue
- feature: add lottery statistics page

## 테스트 원칙

- 기존 기능이 정상 동작하는지 확인한다.
- 변경된 기능만이 아니라 관련 기능도 함께 테스트한다.
- 운영 환경 배포 전 로컬 또는 Codespaces에서 검증한다.
