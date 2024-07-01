<?php
/*
██╗  ██╗██╗███████╗███╗   ██╗██████╗ ███████╗██╗   ██╗
██║ ██╔╝██║██╔════╝████╗  ██║██╔══██╗██╔════╝██║   ██║
█████╔╝ ██║█████╗  ██╔██╗ ██║██║  ██║█████╗  ██║   ██║
██╔═██╗ ██║██╔══╝  ██║╚██╗██║██║  ██║██╔══╝  ╚██╗ ██╔╝
██║  ██╗██║███████╗██║ ╚████║██████╔╝███████╗ ╚████╔╝ 
╚═╝  ╚═╝╚═╝╚══════╝╚═╝  ╚═══╝╚═════╝ ╚══════╝  ╚═══╝  
		Copyright © 2024 - 2025 KienDev 
*/

namespace KienDev\Kit\Commands;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginOwned;
use pocketmine\command\{Command, CommandSender};
use pocketmine\console\ConsoleCommandSender;
use KienDev\Kit\Main;
use KienDev\Kit\FormAPI\{Form, FormAPI, SimpleForm, CustomForm, ModalForm};

class GiveKitCMD extends Command implements PluginOwned{

	private Main $plugin;

	public function getOwningPlugin(): Main{
		return $this->plugin;
	}
	
	 public function __construct(Main $plugin){
		$this->plugin = $plugin;
		parent::__construct("givekit", "Give Kit", null, []);
		$this->setPermission("kit.give");
	}

	public function execute(CommandSender $player, string $label, array $args){
		if(!isset($args[0])){
			$player->sendMessage("Use: /givekit <Kit> <Player>");
			return;
		}
		if($this->plugin->checkKitExists($args[0])==false){
			$player->sendMessage("This Kit Isn't Exists");
			return;
		}
		if(!isset($args[1])){
			$player->sendMessage("Use: /givekit <Kit> <Player>");
			return;
		}
		if($this->plugin->checkPlayerOnline($args[1])==false){
			$player->sendMessage("Player Isn't Online");
			return;
		}
		$player2=$this->plugin->getServer()->getPlayerByPrefix($args[1]);
		$id=$this->plugin->checkIdKit((string)($args[0]));
		$this->plugin->giveKit($player2,(int)($id));
		$player->sendMessage("Gave Kit Successfully!");
	}
}
