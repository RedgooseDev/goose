## Introduce
App 모듈은 Nest를 그룹으로 묶기위해 사용되는 모듈입니다.  
[About the Goose](http://redgoosedev.github.io/goose/#Introduce/AboutTheGoose) 페이지에서 설명한대로 여러가지 스타일의 사이트나 앱을 만들때 nest나 article들을 묶어서 관리하기 위하여 만들었습니다.  
프로젝트 이름으로 사용하는것을 권장합니다.


## URL guide

#### 앱 목록
`{goose}/App/`  
`{goose}/App/index/`

#### App 만들기
`{goose}/App/create/`

#### App 수정
`{goose}/App/modify/{srl}/`

#### App 삭제
`{goose}/App/remove/{srl}/`



## setting.json

모듈의 환경설정 파일입니다. 설정에 대한 소개는 다음과 같습니다.

#### name
모듈의 id값

#### title
출력되는 제목값

#### description
모듈의 설명

#### permission
접근권한 번호 (숫자가 높을수록 권한이 높습니다.)

#### adminPermission
모듈 관리자 권한 번호 (숫자가 높을수록 권한이 높습니다.)

#### install
인스톨이 필요한 모듈인지에 대한 유무를 정합니다.

#### skin
다른형태로 목록이나 폼 페이지가 출력되는 스킨값

#### listStyle
`value : default|card`

목록 스타일



## Database field

App 모듈을 설치할때 사용되는 db 필드들입니다.

| Field      | Type       | Comment
| : -------: | :--------: | :----------------------------
| srl        | int        | 고유번호
| id         | varchar    | 고유 id값
| name       | varchar    | 이름
| regdate    | varchar    | 날짜



## Module API

모듈에서 제공하는 api입니다. 우선 다음과 같이 모듈 인스턴스 변수값에 담아야합니다.
```
$app = new mod\App\App();
$app = core\Module::load('App');
```

#### $app->transaction()
글을 등록하거나 수정, 삭제 처리합니다.
```
$result_make = $app->transaction('create', $_POST); // make
$result_modify = $app->transaction('modify', $_POST); // modify
$result_remove = $app->transaction('remove', $_POST); // remove
```
`$_POST`값에 대해서는 `/mod/App/skin/default/form.blade.php` 파일을 참고해주세요.