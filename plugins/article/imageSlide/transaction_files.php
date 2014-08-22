<?php
if(!defined("GOOSE")){exit();}

/**
 * upload file db update
 * tempFiles 테이블에 있는 임시파일들 목록을 files 테이블에 옮기고, 썸네일으로 사용하는 첨부파일 번호를 리턴한다.
 * 
 * @param {Number} $art_srl : 글을 등록하고 바로 가져온 srl번호
 * @param {Number} $thum_srl : 썸네일 srl번호
 * @return {Number} $thumnail_srl : 바뀐 썸네일 srl번호
 */
function fileUpload($art_srl, $thum_srl)
{
	global $goose;
	if ($_POST['addQueue'])
	{
		$thumnail_srl = null;
		$queue = explode(',', $_POST['addQueue']);
		foreach($queue as $k=>$v)
		{
			if ($v)
			{
				$tempFile = $goose->spawn->getItem(array(
					'table' => 'tempFiles',
					'where' => 'srl='.(int)$v
				));
				$goose->spawn->insert(array(
					'table' => 'files',
					'data' => array(
						'srl' => null,
						'article_srl' => $art_srl,
						'name' => $tempFile['name'],
						'loc' => $tempFile['loc']
					)
				));

				if ($tempFile['srl'] == $thum_srl)
				{
					$thumnail_srl = $goose->spawn->conn->lastInsertId();
				}
				$goose->spawn->delete(array(
					'table' => 'tempFiles',
					'where' => 'srl='.(int)$v
				));
			}
		}
	}
	return ($thumnail_srl) ? $thumnail_srl : $thum_srl;
}

/**
 * upload thumnail
 * 썸네일 이미지 데이터를 받아서 서버에 올리고, 이미지 경로를 리턴한다.
 * 
 * @param {String} $imgData : base64형식의 이미지 데이터
 * @return {String} $thumnailDir : 서버에 업로드한 썸네일 이미지 경로
 */
function uploadThumnail($imgData=null)
{
	global $goose, $dataThumnailDirectory;
	if ($imgData)
	{
		$fileName = uniqid() . ".jpg";
		$month = Date(Ym);
		$thumnailDir = $month.'/'.$fileName;
		$uploadDir = PWD.$dataThumnailDirectory.$month.'/';
		$goose->util->createDirectory($uploadDir, 0777);
		$uploadDir .= $fileName;
		$imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
		$imgData = str_replace(' ', '+', $imgData);
		$success = file_put_contents($uploadDir, base64_decode($imgData));
	}
	return $thumnailDir;
}


// action
switch($paramAction)
{
	// create
	case 'create':
		// get last id number
		$article_srl = $goose->spawn->conn->lastInsertId();

		// files upload
		$thumnail_srl = fileUpload($article_srl, $_POST['thumnail_srl']);

		// thumnail image upload
		if ($thumnail_srl)
		{
			$thumnailUrl = uploadThumnail($_POST['thumnail_image']);
			$goose->spawn->update(array(
				'table' => 'articles',
				'where' => 'srl='.$article_srl,
				'data' => array(
					"thumnail_srl='$thumnail_srl'",
					"thumnail_url='$thumnailUrl'"
				)
			));
		}
		break;

	// modify
	case 'modify':
		// get article item data
		$article = $goose->spawn->getItem(array(
			'table' => 'articles',
			'where' => 'srl='.(int)$_POST['article_srl']
		));

		$absoluteThumnailDir = PWD.$dataThumnailDirectory;

		// upload files
		$thumnail_srl = fileUpload($_POST['article_srl'], $_POST['thumnail_srl']);

		// upload thumnail image
		if ($_POST['thumnail_image'])
		{
			if (file_exists($absoluteThumnailDir.$article['thumnail_url']))
			{
				unlink($absoluteThumnailDir.$article['thumnail_url']);
			}
			$thumnailUrl = uploadThumnail($_POST['thumnail_image']);

			$goose->spawn->update(array(
				'table' => 'articles',
				'where' => 'srl='.(int)$_POST['article_srl'],
				'data' => array(
					"thumnail_srl=$thumnail_srl",
					"thumnail_url='$thumnailUrl'"
				)
			));
			$thumnailUploaded = true;
		}

		// update article
		$result = $goose->spawn->update(array(
			'table' => 'articles'
			,'where' => 'srl='.(int)$_POST['article_srl']
			,'data' => array("thumnail_coords='$_POST[thumnail_coords]'")
		));

		// 썸네일 이미지는 있고, 첨부파일이 하나도 없을때 썸네일 이미지 삭제
		if ($article['thumnail_srl'] && !$thumnailUploaded)
		{
			// get article item data
			$filesCount = $goose->spawn->getCount(array(
				'table' => 'files',
				'where' => 'article_srl='.(int)$_POST['article_srl'].' and srl='.(int)$article['thumnail_srl']
			));
			if (!$filesCount)
			{
				// delete thumnail file
				if (file_exists($absoluteThumnailDir.$article['thumnail_url']))
				{
					unlink($absoluteThumnailDir.$article['thumnail_url']);
				}
				// update article db
				$result = $goose->spawn->update(array(
					'table' => 'articles',
					'where' => 'srl='.(int)$_POST['article_srl'],
					'data' => array(
						"thumnail_srl='0'",
						"thumnail_url=''",
						"thumnail_coords=''"
					)
				));
			}
		}
		break;

	// delete
	case 'delete':
		// delete thumnail image
		if ($article['thumnail_url'] and file_exists(PWD.$dataThumnailDirectory.$article['thumnail_url']))
		{
			unlink(PWD.$dataThumnailDirectory.$article['thumnail_url']);
		}

		// delete original files
		$files = $goose->spawn->getItems(array(
			'table' => 'files',
			'where' => 'article_srl='.$article['srl']
		));
		if (count($files))
		{
			foreach ($files as $k=>$v)
			{
				if (file_exists(PWD.$dataOriginalDirectory.$v['loc']))
				{
					unlink(PWD.$dataOriginalDirectory.$v['loc']);
					$goose->spawn->delete(array(
						'table' => 'files',
						'where' => 'srl='.$v['srl']
					));
				}
			}
		}
		break;
}
?>