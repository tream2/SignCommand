<?php

namespace tream;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\level\Level;

class SignCommand extends PluginBase implements Listener{
	public function onEnable(){
		@mkdir($this->getDataFolder());
        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->db = $this->data->getAll ();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onSign(SignChangeEvent $event){
		if ($event->getLine(0) == "표지판명령"){
			$player = $event->getPlayer();
            $block = $event->getBlock ();
            $level = $block->getLevel();
            $x = $block->x;
            $y = $block->y;
            $z = $block->z;			
            if (!$player->isOp()){
				$player->sendMessage("§f당신은 권한이 없습니다.");
				$event->setCancelled ();
				return true;
			}
			if ($event->getLine(1) == null){
				$player->sendMessage("§f명령어를 적어주세요.");
				$event->setCancelled ();
				return true;
			}
			$this->SignData($x, $y, $z, $event->getLine(1));
			$event->setLine ( 0, "§f[ 표지판 명령 ]");
			$event->setLine ( 1, "§f".$event->getLine(1)."");
			$event->setLine ( 3, "§f터치 해주세요 [!]");
		}
	}
	public function onTouch(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock ();
		$level = $block->getLevel();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
		if ($block->getId() == Block::SIGN_POST or $block->getId() == Block::WALL_SIGN){
			if(isset($this->db [$x.":".$y.":".$z])){
				$this->getServer()->getCommandMap()->dispatch($player, $this->db [$x.":".$y.":".$z] ["command"]);
				$event->setCancelled();
				return true;
			}
		}
	}
	public function onbreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock ();
		$level = $block->getLevel();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
		if ($block->getId() == Block::SIGN_POST or $block->getId() == Block::WALL_SIGN){
			if(isset($this->db [$x.":".$y.":".$z])){
				if(!$player->isOP()){
				$player->sendMessage("§f당신은 권한이 없습니다.");
				$event->setCancelled ();
				return true;
				}
				unset($this->db [$x.":".$y.":".$z]);
				$this->onSave();
				$player->sendMessage("성공적으로 제거했습니다.");
			}
		}
	}
	public function SignData($x, $y, $z, $command){
		$this->db [$x.":".$y.":".$z] = [ ];
        $this->db [$x.":".$y.":".$z] ["command"] = $command;
        $this->onSave();   
    }
    public function onSave(){
      $this->data->setAll($this->db);
      $this->data->save();
    }
}
?>