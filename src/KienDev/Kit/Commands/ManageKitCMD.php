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
use pocketmine\item\enchantment\{Enchantment, StringToEnchantmentParser};
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\{Item, ItemBlock, StringToItemParser, LegacyStringToItemParser, LegacyStringToItemParserException};
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\{InvMenuTransaction,InvMenuTransactionResult};
use muqsit\invmenu\type\InvMenuTypeIds;
use Closure;
use KienDev\Kit\Main;
use KienDev\Kit\FormAPI\{Form, FormAPI, SimpleForm, CustomForm, ModalForm};

class ManageKitCMD extends Command implements PluginOwned{

	private Main $plugin;

	public function getOwningPlugin(): Main{
		return $this->plugin;
	}
	
	 public function __construct(Main $plugin){
		$this->plugin = $plugin;
		parent::__construct("managekit", "Manage Kit", null, []);
		$this->setPermission("kit.manage");
	}

	public function execute(CommandSender $player, string $label, array $args){
		if(!$player instanceof Player){
			$player->sendMessage("Use Command In Game");
			return;
		}
		if(!isset($args[0])){
			$player->sendMessage("Use: /managekit <Kit>");
			return;
		}
		if($this->plugin->checkKitExists($args[0])==false){
			$player->sendMessage("This Kit Isn't Exists");
			return;
		}
		$id=$this->plugin->checkIdKit((string)($args[0]));
		$this->manageKitForm($player, (int)($id));
	}

	public function manageKitForm($player, int $id){
		$kits=$this->plugin->getConfig()->get("kits");
		$kit=$kits[$id];
		$form=new SimpleForm(function(Player $player, $data) use($kit, $id){
			if($data==null) return;
			switch($data){
				case 0:
					break;
				case 1:
					$this->menuAcceptAddItem($player, (int)($id));
					break;
				case 2:
					$this->menuRemoveItem($player, (int)($id), 1);
					break;
			/**	case 3:
					$this->menuSetName($player, (int)$id);
					break;
				case 4:
					$this->menuSetPermission($player, (int)$id);
					break;
				case 5:
					$this->menuSetInfo($player, (int)$id);
					break;
				case 6:
					$this->menuSetMoney($player, (int)$id);
					break;
				case 7:
					$this->menuSetCoin($player, (int)$id);
					break;
				case 8:
					$this->menuRemoveKit($player, (int)$id);
					break;  **/  
				#I'm very lazy to code this =))
			}
		});
		$kitName=$kit["name"];
		$form->setTitle("§l§c♦§e Manage Kit | ".$kitName." §c♦");
		$form->addButton("Exit");
		$form->addButton("Add Item");
		$form->addButton("Remove Item");
	/**	$form->addButton("Set Name");
		$form->addButton("Set Permission");
		$form->addButton("Set Info");
		$form->addButton("Set Money");
		$form->addButton("Set Coin");
		$form->addButton("Remove Kit");  **/
		$form->sendToPlayer($player);
	}

/**	public function menuSetName(Player $player, int $id){
		$kits=$this->plugin->getConfig()->get("kits");
		$kit=$kits[$id];
		$kitName=$kit["name"];
		$form=new CustomForm(function(Player $player, $data) use($id){
			if($data==null) return;
			if(!isset($data[0])){
				$player->sendMessage("Please Enter New Name");
				return;
			}
			if($this->plugin->checkKitExists($data[0])==true){
				$player->sendMessage("This Name Was Existed");
				return;
			}
			$this->plugin->setNameKit((int)$id, (string)$data[0]);
			$player->sendMessage("Renamed Successfully");
			return;
		});
		$form->setTitle("Rename Kit | ".$kitName);
		$form->addInput("- Enter New Name: ");
		$form->sendToPlayer($player);
		return;
	}  **/

	public function menuAcceptAddItem(Player $player, int $id){
		$itemInHand=$player->getInventory()->getItemInHand();
		if(!$itemInHand instanceof Item and !$itemInHand instanceof ItemBlock){
			$player->sendMessage("Please Hold Item In Hand");
			return;
		}
		$kits=$this->plugin->getConfig()->get("kits");
		$kit=$kits[$id];
		$kitName=$kit["name"];
		$menu=InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName("§l§bAccept Add Item | ".$kitName);
		$menu->setListener(function (InvMenuTransaction $transaction) use ($id, $itemInHand) {
			return $this->menuAcceptAddItemListener($transaction, $id, $itemInHand);
		});
		$inv=$menu->getInventory();
		$inv->setItem(11, LegacyStringToItemParser::getInstance()->parse("160:5")->setCustomName("§l§aYES")->setCount(1));
		$inv->setItem(15, LegacyStringToItemParser::getInstance()->parse("160:14")->setCustomName("§l§cNO")->setCount(1));
		$inv->setItem(13, $itemInHand);
		$menu->send($player);
	}

	public function menuAcceptAddItemListener(InvMenuTransaction $transaction, int $id, Item|ItemBlock $itemInHand) : InvMenuTransactionResult{
		$player=$transaction->getPlayer();
		$action=$transaction->getAction();
		$inv=$action->getInventory();
		$slot=$action->getSlot();
		$item=$inv->getItem($slot);
		if($item->getCustomName()==="§l§cNO"){
			$player->removeCurrentWindow($inv);
			return $transaction->discard();
		}
		if($item->getCustomName()==="§l§aYES"){
			$this->plugin->addItemKit((int)($id), $itemInHand);
			$player->removeCurrentWindow($inv);
			$player->sendMessage("Added Successfully");
			return $transaction->discard();
		}
		return $transaction->discard();
	}

	public function menuRemoveItem(Player $player, int $id, int $page){
		$kit=$this->plugin->getConfig()->getNested("kits")[$id];
		$kitName=$kit["name"];
		$items=$kit["items"];
		if(empty($items)){
			$player->sendMessage("This Kit Dont Have Any Item");
			return;
		}
		$all=$items;
		$totalpage=ceil(count($all)/44);
		$begin=(int)(($page-1)*44)+1;
		$end=min($begin+43, count($all));
		$i=1;
		$slot=0;
		$menu=InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName("§l§bChoose Item To Remove | ".$kitName);
		$menu->setListener(function (InvMenuTransaction $transaction) use ($id, $page, $totalpage) {
			return $this->menuRemoveItemListener($transaction, $id, $page, $totalpage);
		});
		$inv=$menu->getInventory();
		foreach($items as $data){
			if($i>=$begin && $i<=$end){
				if($slot<$inv->getSize() && $slot<=44){
					$item=$this->plugin->dataToItem($data);
					$inv->setItem($slot, $item);
					++$slot;
				}
			}
			++$i;
		}
		$inv->setItem(45, LegacyStringToItemParser::getInstance()->parse("339")->setCustomName("Previous Page")->setCount(1));
		$inv->setItem(46, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(47, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(48, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(49, LegacyStringToItemParser::getInstance()->parse("340")->setCustomName("Page: ".$page."/".$totalpage)->setCount(1));
		$inv->setItem(50, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(51, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(52, LegacyStringToItemParser::getInstance()->parse("160:15")->setCustomName(" ")->setCount(1));
		$inv->setItem(53, LegacyStringToItemParser::getInstance()->parse("339")->setCustomName("Next Page")->setCount(1));
		$menu->send($player);
	}

	public function menuRemoveItemListener(InvMenuTransaction $transaction, int $id, int $page, int $totalpage) : InvMenuTransactionResult{
		$player=$transaction->getPlayer();
		$action=$transaction->getAction();
		$inv=$action->getInventory();
		$slot=$action->getSlot();
		$item=$inv->getItem($slot);
		if ($item->isNull()){
        	return $transaction->discard();
    	}
		if($item->getCustomName()=="Previous Page"){
			if($page>1){
				$player->removeCurrentWindow($inv);
				$this->menuRemoveItem($player, $id, (int)$page-1);
			}
			return $transaction->discard();
		}
		if($item->getCustomName()=="Next Page"){
			if($page<$totalpage){
				$player->removeCurrentWindow($inv);
				$this->menuRemoveItem($player, $id, (int)$page+1);
			}
			return $transaction->discard();
		}
		if($item->getCustomName()!=" " && $item->getCustomName()!="Page: ".$page."/".$totalpage){
			$player->removeCurrentWindow($inv);
			$this->menuAcceptRemoveItem($player, $id, $item);
			return $transaction->discard();
		}
		return $transaction->discard();
	}

	public function menuAcceptRemoveItem(Player $player, int $id, Item|ItemBlock $item){
		$kits=$this->plugin->getConfig()->get("kits");
		$kit=$kits[$id];
		$kitName=$kit["name"];
		$menu=InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName("§l§bAccept Remove Item | ".$kitName);
		$menu->setListener(function (InvMenuTransaction $transaction) use ($id, $item) {
			return $this->menuAcceptRemoveItemListener($transaction, $id, $item);
		});
		$inv=$menu->getInventory();
		$inv->setItem(11, LegacyStringToItemParser::getInstance()->parse("160:5")->setCustomName("§l§aYES")->setCount(1));
		$inv->setItem(15, LegacyStringToItemParser::getInstance()->parse("160:14")->setCustomName("§l§cNO")->setCount(1));
		$inv->setItem(13, $item);
		$menu->send($player);
	}

	public function menuAcceptRemoveItemListener(InvMenuTransaction $transaction, int $id, Item|ItemBlock $item) : InvMenuTransactionResult{
		$player=$transaction->getPlayer();
		$action=$transaction->getAction();
		$inv=$action->getInventory();
		$slot=$action->getSlot();
		$itemc=$inv->getItem($slot);
		if($itemc->getCustomName()==="§l§cNO"){
			$player->removeCurrentWindow($inv);
			return $transaction->discard();
		}
		if($itemc->getCustomName()==="§l§aYES"){
			$this->plugin->removeItemKit((int)($id), $item);
			$player->removeCurrentWindow($inv);
			$player->sendMessage("Removed Successfully");
			return $transaction->discard();
		}
		return $transaction->discard();
	}
}
