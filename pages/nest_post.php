<?php
if(!defined("GOOSE")){exit();}

$nest_srl = (isset($routePapameters['param0'])) ? (int)$routePapameters['param0'] : null;
if ($paramAction !== "create" and $nest_srl)
{
	$nest = $spawn->getItem(array(
		'table' => $tablesName['nests'],
		'where' => 'srl='.$nest_srl
	));
	$thumnailSize = explode("*", $nest['thumnailSize']);
}

if ($thumnailSize[1])
{
	$thumnailSize = array('width' => $thumnailSize[0], 'height' => $thumnailSize[1]);
}
else
{
	$thumnailSize = array('width'=>100, 'height'=>100);
}

$listCount = ($nest['listCount']) ? $nest['listCount'] : 12;
$titleType = ($paramAction == 'create') ? '만들기' : '';
$titleType = ($paramAction == 'modify') ? '수정' : $titleType;
$titleType = ($paramAction == 'delete') ? '삭제' : $titleType;
?>

<section class="form">
	<div class="hgroup">
		<h1>둥지<?=$titleType?></h1>
	</div>
	<form action="<?=ROOT?>/nest/<?=$paramAction?>/" method="post" id="regsterForm">
		<input type="hidden" name="nest_srl" value="<?=$nest_srl?>" />
		<?
		if ($paramAction == "delete")
		{
		?>
			<fieldset>
				<legend class="blind">둥지<?=$titleType?></legend>
				<p class="message">"<?=$nest['name']?>"둥지를 삭제하시겠습니까? 삭제된 둥지는 복구할 수 없습니다.</p>
			</fieldset>
		<?
		}
		else
		{
		?>
			<fieldset>
				<legend class="blind">둥지<?=$titleType?></legend>
				<?
				$group = $spawn->getItems(array(
					'table' => $tablesName['nestGroups'],
					'order' => 'srl',
					'sort' => 'asc'
				));

				if (count($group))
				{
				?>
					<dl class="table">
						<dt><label for="group_srl">둥지그룹</label></dt>
						<dd>
							<select name="group_srl" id="group_srl">
								<option value="0">선택하세요.</option>
								<?
								foreach ($group as $k=>$v)
								{
									$selected = ($v['srl'] == $nest['group_srl']) ? ' selected="selected"' : '';
									echo "<option value=\"$v[srl]\"$selected>$v[name]</option>";
								}
								?>
							</select>
						</dd>
					</dl>
				<?
				}
				?>
				<dl class="table">
					<?
					$attr = ($nest['id']) ? ' value="'.$nest['id'].'" readonly' : '';
					?>
					<dt><label for="id">아이디</label></dt>
					<dd>
						<input type="text" name="id" id="id" maxlength="20" size="22" placeholder="영문과 숫자 입력가능"<?=$attr?>/>
						<p>이 항목은 한번정하면 변경할 수 없습니다.</p>
					</dd>
				</dl>
				<dl class="table">
					<dt><label for="name">둥지이름</label></dt>
					<dd><input type="text" name="name" id="name" maxlength="100" size="22" value="<?=$nest['name']?>"/></dd>
				</dl>
				<dl class="table">
					<dt><label for="thumWidth">썸네일사이즈</label></dt>
					<dd>
						<p style="margin:0 0 5px">
							<label>가로 : <input type="text" name="thumWidth" id="thumWidth" maxlength="4" size="5" value="<?=$thumnailSize['width']?>"/></label>
						</p>
						<p style="margin:5px 0 0">
							<label>세로 : <input type="text" name="thumHeight" id="thumHeight" maxlength="4" size="5" value="<?=$thumnailSize['height']?>"/></label>
						</p>
					</dd>
				</dl>
				<dl class="table">
					<dt><label>썸네일 축소방식</label></dt>
					<dd>
						<?
						if ($paramAction == "modify")
						{
							$thumType1 = ($nest['thumnailType'] == "crop") ? ' checked = "checked"' : '';
							$thumType2 = ($nest['thumnailType'] == "resize") ? ' checked = "checked"' : '';
							$thumType3 = ($nest['thumnailType'] == "resizeWidth") ? ' checked = "checked"' : '';
							$thumType4 = ($nest['thumnailType'] == "resizeHeight") ? ' checked = "checked"' : '';
						}
						else
						{
							$thumType1 = ' checked = "checked"';
						}
						?>
						<label><input type="radio" name="thumType" id="thumType1" value="crop"<?=$thumType1?>/> 자르기</label>
						<label><input type="radio" name="thumType" id="thumType2" value="resize"<?=$thumType2?>/> 리사이즈</label>
						<label><input type="radio" name="thumType" id="thumType3" value="resizeWidth"<?=$thumType3?>/> 리사이즈(가로기준)</label>
						<label><input type="radio" name="thumType" id="thumType4" value="resizeHeight"<?=$thumType4?>/> 리사이즈(세로기준)</label>
					</dd>
				</dl>
				<dl class="table">
					<dt><label for="listCount">목록수</label></dt>
					<dd>
						<input type="tel" name="listCount" id="listCount" maxlength="3" size="4" value="<?=$listCount?>"/>
						<p>한페이지에 출력되는 글 갯수입니다.</p>
					</dd>
				</dl>
				<dl class="table">
					<?
					$useCategoryYes = ($nest['useCategory'] == 1) ? ' checked = "checked"' : '';
					$useCategoryNo = ($nest['useCategory'] != 1) ? ' checked = "checked"' : '';
					?>
					<dt><label for="useCategory">분류사용</label></dt>
					<dd>
						<label><input type="radio" name="useCategory" id="useCategory" value="0" <?=$useCategoryNo?>/> 사용안함</label>
						<label><input type="radio" name="useCategory" value="1" <?=$useCategoryYes?>/> 사용</label>
					</dd>
				</dl>
				<dl class="table">
					<?
					$useExtraVarYes = ($nest['useExtraVar'] == 1) ? ' checked = "checked"' : '';
					$useExtraVarNo = ($nest['useExtraVar'] != 1) ? ' checked = "checked"' : '';
					?>
					<dt><label for="useExtraVar">확장변수사용</label></dt>
					<dd>
						<label><input type="radio" name="useExtraVar" id="useExtraVar" value="0" <?=$useExtraVarNo?>/> 사용안함</label>
						<label><input type="radio" name="useExtraVar" value="1" <?=$useExtraVarYes?>/> 사용</label>
					</dd>
				</dl>
				<dl class="table">
					<dt><label for="editor">에디터 선택</label></dt>
					<dd>
						<select name="editor" id="editor">
							<?
							$tree = $util->readDir(PWD.'/plugins/editor/');
							echo (!count($tree)) ? "<option>에디터 없음</option>" : "";
							foreach($tree as $k=>$v)
							{
								$selected = ($v == $nest['editor']) ? ' selected' : '';
								echo "<option value=\"$v\"$selected>$v</option>";
							}
							?>
						</select>
					</dd>
				</dl>
			</fieldset>
		<?
		}
		?>
		<nav class="btngroup">
			<span><button type="submit" class="ui-button btn-highlight"><?=$titleType?></button></span>
			<span><button type="button" class="ui-button" onclick="history.back(-1)">뒤로가기</button></span>
		</nav>
	</form>
</section>

<?
if ($paramAction != "delete")
{
?>
	<script src="<?=$jQueryAddress?>"></script>
	<script src="<?=ROOT?>/libs/ext/validation/jquery.validate.min.js"></script>
	<script src="<?=ROOT?>/libs/ext/validation/localization/messages_ko.js"></script>
	<script>
	jQuery(function($){
		$.validator.addMethod("alphanumeric", function(value, element) {
			return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
		});
		$('#regsterForm').validate({
			rules : {
				id : {required : true, minlength : 2, alphanumeric : true}
				,name : {required: true, minlength: 2}
				,thumWidth : {required: true, minlength: 2, number: true}
				,thumHeight : {required: true, minlength: 2, number: true}
				,listCount : {required: true, number: true}
			}
			,messages : {
				id : {alphanumeric: '알파벳과 숫자만 사용가능합니다.'}
			}
		});
	});
	</script>
<?
}
?>