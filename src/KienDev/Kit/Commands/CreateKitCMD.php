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

class CreateKitCMD extends Command implements PluginOwned{

	private Main $plugin;

	public function getOwningPlugin(): Main{
		return $this->plugin;
	}
	
	 public function __construct(Main $plugin){
		$this->plugin = $plugin;
		parent::__construct("createkit", "Create Kit", null, []);
		$this->setPermission("kit.create");
	}

	public function execute(CommandSender $player, string $label, array $args){
		if(!$player instanceof Player){
			$player->sendMessage("Use Command In Game");
			return;
		}
		if(!isset($args[0])){
			$player->sendMessage("Use: /createkit <Kit>");
			return;
		}
		if($this->plugin->checkKitExists($args[0])==true){
			$player->sendMessage("This Kit Is Exists");
			return;
		}
		$this->acceptForm($player, $args[0]);
	}

	public function acceptForm(Player $player,string $kit){
		$kits=$this->plugin->getConfig()->getNested("kits");
		$form=new SimpleForm(function(Player $player, $data) use($kit){
			if($data==null or $data==0) return;
			if($data==1){
				$this->plugin->createKit($kit);
				$player->sendMessage("Created SuccessFully!");
			}
		});
		$form->setTitle("§l§c♦§e Create Kit §c♦");
		$form->setContent("§l§9Do You Want To Create ".$kit." Kit?");
		$form->addButton("§l§4✘ No ✘");
		$form->addButton("§l§2✔ Yes ✔");
		$form->sendToPlayer($player);
	}
}