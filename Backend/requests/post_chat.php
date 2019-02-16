<?php
require_once(__DIR__ .'/../includes/autoload.php');
if($_POST['token_id'] != $_SESSION['token_id']) {
	return false;
}

// Remove any extra white spaces, new lines
if(isset($_POST['message'])) {
    $_POST['message'] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $_POST['message']);
} else {
    $_POST['message'] = '';
}

// If message is not empty
if(!empty($_POST['id'])) {
	if($user['username']) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->time = $settings['time'];
		$feed->id = $user['idu'];
		$feed->chat_length = $settings['message'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->max_size = $settings['sizemsg'];
		$feed->image_format = $settings['formatmsg'];
		$feed->message_length = $settings['message'];
		$feed->max_images = $settings['ilimit'];
		$feed->plugins = loadPlugins($db);

		if(!empty($_POST['message']) && $_POST['message'] !== ' ' && isset($_POST['type']) == false) {
			$response = $feed->postChat($_POST['message'], $_POST['id']);
			if( $response !== false ){
			  $message = [
				'from' => $user['idu'],
				'destination' => $_POST['id'],
				'message' => $_POST['message'],
				'image' => '',
			  ];
			  sendMessage($message);
			}
			echo $response[0];
		  } elseif(isset($_POST['type'])) {
			$response = $feed->postChat($_POST['message'], $_POST['id'], $_POST['type'], (isset($_POST['value']) ? $_POST['value'] : null));
			if( $response !== false ){
			  $message = [
				'from' => $user['idu'],
				'destination' => $_POST['id'],
				'message' => $_POST['message'],
				'image' => $CONF['url'].'/uploads/media/'.$response[1],
			  ];
			  sendMessage($message);
			}
			echo $response[0];
		  }
	}
}

mysqli_close($db);

include __DIR__.'/../api/config.php';
include __DIR__.'/../api/language.php';
function sendMessage($message) {
    global $CONF, $LNG;

    $content = [
      "en" => (( $message['message'] && strlen($message['message']) > 0 )? $message['message']: $LNG['image'])
    ];
    $fields = array(
      'app_id' => $CONF['onesignal']['appid'],
      'data' => [
        'from' => $message['from']
      ],
      'headings' => [
        'en' => $LNG['newmessage'],
      ],
      'contents' => $content,
      'filters' => [
        [
          "field" => "tag", 
          "key" => "userId", 
          "relation" => "=", 
          "value" => $message['destination']
        ],
      ],
      'big_picture' => $message['image'],
      'ios_attachments' => [
        'id' => $message['image']
      ],
    );
    
    $fields = json_encode($fields);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic '.$CONF['onesignal']['restkey']
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>