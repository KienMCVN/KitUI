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

namespace KienDev\Kit;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\item\enchantment\{Enchantment, StringToEnchantmentParser};
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\{Item, ItemBlock, StringToItemParser, LegacyStringToItemParser, LegacyStringToItemParserException};
use KienDev\Kit\FormAPI\{Form, FormAPI, SimpleForm, CustomForm, ModalForm};
use KienDev\Kit\Commands\{KitCMD, GiveKitCMD, CreateKitCMD, ManageKitCMD};
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;

class Main extends PluginBase implements Listener{
	
	public $economyProvider, $coin, $ce;

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		libPiggyEconomy::init();
		$this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));
		$this->coin=$this->getServer()->getPluginManager()->getPlugin("CoinAPI");
		$this->ce=$this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
		$this->saveDefaultConfig();
		$this->getServer()->getCommandMap()->register("/kits", new KitCMD($this));
		$this->getServer()->getCommandMap()->register("/givekit", new GiveKitCMD($this));
		$this->getServer()->getCommandMap()->register("/createkit", new CreateKitCMD($this));
		$this->getServer()->getCommandMap()->register("/managekit", new ManageKitCMD($this));
		$this->getLogger()->notice("Plugin KitUI By KienDev Is On Enable");
	}
	
	public function getEconomyProvider(){
		return $this->economyProvider;
	}

	public function addPlayerUsed(Player $player,int $id){
		$kits=$this->getConfig()->getNested("kits");
		$kit=$kits[$id];
		$used=$kit["used-by"];
		if(!empty($used)){
			$used="$used, ".$player->getName();
		}else{
			$used=$player->getName();
		}
		$kits[$id]["used-by"]=$used;
		$this->getConfig()->setNested("kits",$kits);
		$this->getConfig()->save();
		return true;
	}

	public function checkPlayerUsed(Player $player,int $id){
		$kits=$this->getConfig()->getNested("kits");
		$kit=$kits[$id];
		$used=$kit["used-by"];
		if(empty($used)) return false;
		$used=explode(", ", $used);
		if(in_array($player->getName(), $used)) return true;
		return false;
	}

	public function checkPlayerOnline(string $data){
		$player=$this->getServer()->getPlayerByPrefix($data);
		if($player==null) return false;
		return true;
	}

	public function checkKitExists(string $data){
		$kits=$this->getConfig()->getNested("kits");
		$exists=false;
		foreach($kits as $kit){
	        if($kit["name"]===$data){
				$exists=true;
				break;
			}
		}
		return $exists;
	}

	public function checkIdKit(string $data): ?int{
		$kits=$this->getConfig()->getNested("kits");
		$id=array_search($data,array_column($kits,"name"));
		return $id;
	}

	public function checkPerm(Player $player,string $perm){
		if($player->hasPermission((string)($perm)) or $this->getServer()->isOp($player->getName())) return true;
		return false;
	}

	public function itemToData(Item|ItemBlock $item): string {
        $cloneItem = clone $item;
        $itemNBT = $cloneItem->nbtSerialize();
        return base64_encode(serialize($itemNBT));
    }

    public function dataToItem(string $item): Item|ItemBlock{
        $itemNBT = unserialize(base64_decode($item));
        return Item::nbtDeserialize($itemNBT);
    }

    public function setNameKit(int $id, string $newName){
    	$kits=$this->getConfig()->get("kits");
		$kit=$kits[$id];
		$kit["name"]=$newName;
		$kits[$id]=$kit;
		$this->getConfig()->set("kits", $kits);
		$this->getConfig()->save();
		return;
    }

	public function addItemKit(int $id, Item|ItemBlock $item){
		$itemData=$this->itemToData($item);
		$kits=$this->getConfig()->get("kits");
		$kit=$kits[$id];
		$kit["items"][]=$itemData;
		$kits[$id]=$kit;
		$this->getConfig()->set("kits", $kits);
		$this->getConfig()->save();
		return;
	}

	public function removeItemKit(int $id, Item|ItemBlock $item){
		$itemData=$this->itemToData($item);
		$kits=$this->getConfig()->get("kits");
		$kit=$kits[$id];
		$kit["items"]=array_diff($kit["items"], [$itemData]);
		$kits[$id]=$kit;
		$this->getConfig()->set("kits", $kits);
		$this->getConfig()->save();
		return;
	}

	public function createKit(string $kit){
		$newkit=["name" => $kit,
				 "info" => [],
				 "permission" => [],
				 "money" => 0,
				 "coin" => 0,
				 "items" => [],
				 "used-by" => []];
		$kits=$this->getConfig()->get("kits",[]);
		$kits[]=$newkit;
		$this->getConfig()->set("kits",$kits);
		$this->getConfig()->save();
	}

	public function giveKit(Player $player,int $id){
		$player->sendMessage($this->getConfig()->getNested("kits")[$id]["name"]);
		$kit=$this->getConfig()->getNested("kits")[$id];
		$money=$kit["money"];
		$coinn=$kit["coin"];
		if($money>0){
			$this->getEconomyProvider()->giveMoney($player, $money);
			$player->sendMessage("You Was Received ".$money."$ From Kit");
		}
		if($coinn>0){
			$this->coin->addCoin($player, $coinn);
			$player->sendMessage("You Was Received ".$coinn." Coin From Kit");
		}
		$items=$kit["items"];
		if(!empty($items)){
			foreach($items as $data){
				$item=$this->dataToItem($data);
				if(!$player->getInventory()->canAddItem($item)){
					$pos=new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ());
					$player->getPostion()->getWorld()->dropItem($pos,$item);
					continue;
				}else{
					$player->getInventory()->addItem($item);
				}
			}
		}
		return;
	}
}
