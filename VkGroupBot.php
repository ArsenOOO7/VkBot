<?php

/**
 * @author ArsenOOO7
 *
*/

require_once(__DIR__."/Bot.php");

class VkGroupBot{
	
	const API = "https://api.vk.com/method/";
	const VERSION = "5.80";
	
	
	/** @var Array */
	private $longPoll = [];
	/** @var Array */
	private $config = [];
	
	/** @var Bot */
	private $bot;
	
	
	
	
	/**
	* @param Array $config
	* @param Boolean $loop
	*
	*/
	function __construct($config = [], $loop = true){
		
		$this->config = $config;
		$this->bot = new Bot($this);
		
		$this->getLongPoll();
		
		printf("!!! Начинаю работу !!!".PHP_EOL);
		printf(" --- Работаю... --- ".PHP_EOL);
		
		if($loop)
			$this->startWorking();
		
	}
	
	
	
	function getLongPoll(){
		
		$args = [];
		
		$args["access_token"] = $this->config["usrToken"];
		$args["group_id"] = $this->config["group_id"];
		$args["v"] = VkGroupBot::VERSION;
		
		
		$this->longPoll = json_decode(file_get_contents(VkGroupBot::API . "groups.getLongPollServer?".http_build_query($args)), true)["response"];
		
	}
	
	
	
	/**
	* @param String $method
	* @param Array $args
	*
	*/
	function sendRequest($method, $args = []){
		
		$args["access_token"] = $this->config["groupToken"];
		$args["v"] = VkGroupBot::VERSION;
		
		return json_decode(file_get_contents(VkGroupBot::API . $method."?".http_build_query($args)), true);
		
	}
	
	
	
	/**
	* @param Integer $userId
	* @return String
	*
	*/
	function getUserName($userId){
		
		$request = $this->sendRequest("users.get", ["user_ids" => $userId]);
		
		return $request[0]["first_name"]." ".$request[0]["last_name"];
		
	}
	
	
	
	/**
	* @param Integer $peer_id
	* @param String $message
	* @param Integer $repliedId
	*
	*/
	function sendMessage($peer_id, $message, $repliedId = null){
		
		$args["peer_id"] = $peer_id;
		$args["message"] = $message;
		
		if(isset($repliedId) and $this->config["reply"])
			$args["forward_messages"] = $repliedId;
		
		$this->sendRequest("messages.send", $args);
		
	}
	
	
	
	/**
	* @return Bot
	*
	*/
	function getBot(){
		
		return $this->bot;
		
	}
	
	
	
	/**
	* @return Config | Array
	*
	*/
	function getConfig(){
		
		return $this->config;
		
	}
	
	
	
	function startWorking(){
		
		$ts = $this->longPoll["ts"];
		
		while(true){
			
			$server = $this->longPoll["server"];
			$key = $this->longPoll["key"];
			
			$response = json_decode(file_get_contents($server."?act=a_check&key=".$key."&ts=".$ts."&wait=5"), true);
			
			if(isset($response["failed"])){
				
				switch($response["failed"]){
					
					case 1:
					
						$ts = $response["ts"];
						
					break;
					
					case 2:
					case 3:
						
						$this->getLongPoll();
						
					break;
					
				}
				
				continue;
				
			}
			
			$ts = $response["ts"];
			
			foreach($response["updates"] as $update){
				
				switch(array_shift($update)){
					
					case "message_new":
						
						$this->bot->exeMessage($update["object"]);
						
					break;
					
				}
			}
		}
	}
}
