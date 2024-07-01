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

class KitCMD extends Command implements PluginOwned{

	private Main $plugin;

	public function getOwningPlugin(): Main{
		return $this->plugin;
	}
	
	 public function __construct(Main $plugin){
		$this->plugin = $plugin;
		parent::__construct("kits", "Open Kit Menu", null, ["kit"]);
		$this->setPermission("kit.menu");
	}

	public function execute(CommandSender $player, string $label, array $args){
		if(!$player instanceof Player){
			$player->sendMessage("Use Command In Game");
			return;
		}
		$this->kitsForm($player);
	}

	public function kitsForm(Player $player){
		$form=new SimpleForm(function(Player $player, $data){
			if($data==null) return;
			if($data==0) return;
			$this->receiveKit($player, (int)((int)($data)-1));
		});
		$form->setTitle("§l§c♦§e Kit Menu §c♦");
		$form->addButton("§l§c•§9 Exit §c•");
		foreach($this->plugin->getConfig()->getNested("kits") as $kits){
			$namekit = $kits["name"];
			$infokit = $kits["info"];
			if(!empty($infokit)){
				$form->addButton($namekit."\n".$infokit);
			}else{
				$form->addButton($namekit);
			}
		}
		$form->sendToPlayer($player);
	}

	public function receiveKit($player, $id){
		$kits=$this->plugin->getConfig()->getNested("kits");
		$form=new SimpleForm(function(Player $player, $data) use($id, $kits){
			if($data==null or $data==0) return;
			if($data==1){
				if($this->plugin->checkPlayerUsed($player, $id)==true){
					$player->sendMessage("You Received This Kit Ago");
					return;
				}
				$perm=$kits[$id]["permission"];
				if(!empty($perm)){
					if($this->plugin->checkPerm($player, (string)($perm))==false){
						$player->sendMessage("You Dont Have Permission To Receive This Kit");
						return;
					}
				}
				$this->plugin->giveKit($player, $id);
				$this->plugin->addPlayerUsed($player, $id);
				$player->sendMessage("Received SuccessFully!");
			}
		});
		$namekit=$kits[$id]["name"];
		$form->setTitle("§l§c♦§e ".$namekit." §c♦");
		$form->addButton("§l§4✘ No ✘");
		$form->addButton("§l§2✔ Yes ✔");
		$form->sendToPlayer($player);
	}
}
