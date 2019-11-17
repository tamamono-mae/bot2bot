<?php

	$response = file_get_contents('php://input');
	$update = json_decode($response, true);
	
	$rxtoken = 'the bot who is be call';
	$txtoken = 'the bot who is sending message';
	$chat_id = $update['message']['from']['id'];
	
	if (array_key_exists('text', $update['message'])) {
		//$msg = 'text';
		$not_data = true;
		$method = 'sendMessage';
		$send_key = 'text';
		$msg = $update['message']['text'];
	}elseif(array_key_exists('sticker', $update['message'])){
		//$msg = 'sticker';
		$not_data = true;
		$method = 'sendSticker';
		$send_key = 'sticker';
		$msg = $update['message']['sticker']['file_id'];
	}elseif(array_key_exists('photo', $update['message'])){
		//$msg = 'photo';
		$not_data = false;
		$method = 'sendPhoto';
		$send_key = 'photo';
		$mine_type = 'image/jpeg';
		//A file id from owner is used by owner only!
		if(array_key_exists(2 , $update['message']['photo'])){
			$file_id = $update['message']['photo'][2]['file_id'];
		}elseif(array_key_exists(1 , $update['message']['photo'])){
			$file_id = $update['message']['photo'][1]['file_id'];
		}else{
			$file_id = $update['message']['photo'][0]['file_id'];
		}
	}elseif(array_key_exists('document', $update['message'])){
		//$msg = 'document';
		$not_data = false;
		$method = 'sendDocument';
		$send_key = 'document';
		$file_name = $update['message']['document']['file_name'];
		$mine_type = $update['message']['document']['mine_type'];
		$file_id = $update['message']['document']['file_id'];
	}else{
		$not_data = true;
		$method = 'sendMessage';
		$send_key = 'text';
		$msg = $response;
	}
	//$msg = $response;
	//echo $response;
	if($not_data){
		$website = "https://api.telegram.org/bot{$txtoken}/{$method}?chat_id={$chat_id}&{$send_key}={$msg}";
		$update = file_get_contents($website);
	}else{
		$website = "https://api.telegram.org/bot{$rxtoken}/getFile?file_id={$file_id}";
		$response = file_get_contents($website);
		$update = json_decode($response, true);
		$file_path = $update['result']['file_path'];
		
		$file_url = "https://api.telegram.org/file/bot{$rxtoken}/{$file_path}";
		//Download file to temp path
		file_put_contents('R:\file.tmp', fopen($file_url, 'r'));

		$filepath = realpath('R:\file.tmp');
		$cfile = curl_file_create($filepath, $mine_type, $file_name);
		$post = array('chat_id' => $chat_id, $send_key => $cfile);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot" . $txtoken . "/" . $method);
		curl_setopt($ch, CURLOPT_POST, 1);   
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_exec ($ch);
		curl_close ($ch);
		//Delete temp file
		unlink ($filepath);
	}
?>