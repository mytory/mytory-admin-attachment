# Mytory Admin Attachment

워드프레스용 플러그인. 관리자쪽에서 한국식으로 파일을 첨부할 수 있도록 해 준다. 

워드프레스 UI에 익숙하지 않은 관리자들을 위한 플러그인이다.

## 사용할 수 있는 함수

* `void mytory_attachment_list($post_id = NULL)` : 루프 안에서 사용하면 Post ID를 넣지 않아도 된다. 첨부파일 목록을 뿌려 준다.
* `int mytory_attachment_count($post_id = NULL)` : 해당 포스트의 첨부파일 개수를 리턴해 준다.

## 설정

설정에서 이 플러그인을 적용할 `custom_post_type`을 설정할 수 있다.