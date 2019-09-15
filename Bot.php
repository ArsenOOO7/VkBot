<?php

/**
 * @author ArsenOOO7
 *
 */

require_once(__DIR__."/VkGroupBot.php");

class Bot{
	
	/** @var VkGroupBot */
	private $api;
	
	
	
	/**
	* @param VkGroupBot $api
	*
	*/
	function __construct(VkGroupBot $api){
		
		$this->api = $api;
		
	}
	
	
	
	/**
	* @param Array $event
	*
	*/
	function exeMessage($event){
		
		$peerId = $event["peer_id"];
		$message = $event["text"];
		$messageID = $event["id"];
		
		
		$messages = $this->api->getConfig()["messages"];
		
		$answer = "Че ты тут забыл?";
		
		foreach($messages as $msg => $resp){
			
			if($msg == $message){
				
				$answer = $resp;
				break;
				
			}
		}
		
		$this->api->sendMessage($peerId, $answer, $messageID);
	}
}


if(!file_exists(__DIR__."/Config.json")){
	
	$toJson = [
	
		"groupToken" => "",
		"usrToken" => "",
		"group_id" => 0,
		"reply" => false,
		"messages" => [
		
			"привет!" => "Привет!",
			"как дела?" => "Да норм, а у тебя?",
			"чо делаешь?" => "Работаю в отличии от тебя"
			
		]
	];
	
	file_put_contents(__DIR__."/Config.json", json_encode($toJson, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE));
	die("!!! Заполните конфиг !!!");
	
}

$config = json_decode(file_get_contents(__DIR__."/Config.json"), true);
(new VkGroupBot($config));
