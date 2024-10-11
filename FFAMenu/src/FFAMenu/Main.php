<?php

namespace FFAMenu;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\SimpleForm;

class Main extends PluginBase {

    private const NODEBUFF_WORLD = "nodebuff";
    private const SUMO_WORLD = "sumo";   

    public function onEnable(): void {
        $this->loadWorlds();
    }

    private function loadWorlds(): void {
        $worldManager = $this->getServer()->getWorldManager();
        
        if (!$worldManager->isWorldLoaded(self::NODEBUFF_WORLD)) {
            $worldManager->loadWorld(self::NODEBUFF_WORLD);
        }

        if (!$worldManager->isWorldLoaded(self::SUMO_WORLD)) {
            $worldManager->loadWorld(self::SUMO_WORLD);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $command->setPermission("ffa.command");
        if ($command->getName() === "ffa") {
            if ($sender instanceof Player) {
                $this->showFFAUI($sender);
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be executed by a player.");
            }
            return true;
        }
        return false;
    }

    private function teleportToArena(Player $player, string $worldName): void {
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if ($world !== null) {
            $spawn = $world->getSpawnLocation();
            $player->teleport($spawn);
            $player->sendMessage(TextFormat::GREEN . "You have been teleported to the " . $worldName . " arena!");
        } else {
            $player->sendMessage(TextFormat::RED . "The arena world could not be found.");
        }
    }

    private function showFFAUI(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0:
                    $this->teleportToArena($player, self::NODEBUFF_WORLD);
                    $player->sendMessage(TextFormat::GREEN . "Joining Nodebuff FFA...");
                    $this->giveNodebuffKit($player);
                    break;
                case 1:
                    $this->teleportToArena($player, self::SUMO_WORLD);
                    $player->sendMessage(TextFormat::GREEN . "Joining Sumo FFA...");
                    $this->giveSumoKit($player);
                    break;
            }
        });

        $form->setTitle("§1§rFFA Arenas");
        $form->setContent("Choose your FFA arena:");
        $form->addButton("Nodebuff");
        $form->addButton("Sumo");

        $player->sendForm($form);
    }

    public function giveNodebuffKit(Player $player): void {
        $player->getInventory()->clearAll();
    
        $helmet = VanillaItems::DIAMOND_HELMET();
        $chestplate = VanillaItems::DIAMOND_CHESTPLATE();
        $leggings = VanillaItems::DIAMOND_LEGGINGS();
        $boots = VanillaItems::DIAMOND_BOOTS();
    
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggings);
        $player->getArmorInventory()->setBoots($boots);
    
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->setCustomName("§7» §3Nodebuff Sword §7«");
        $player->getInventory()->setItem(0, $sword);

        for ($i = 1; $i < 36; $i++) { 
            $potion = VanillaItems::SPLASH_POTION();
            $potion->setType(PotionType::HEALING); 
            $potion->setCustomName("§7» §6Health Potion §7«");
            $player->getInventory()->setItem($i, $potion);
        }
    
        $player->sendMessage(TextFormat::GREEN . "You have received the Nodebuff kit!");
    }
    
    public function giveSumoKit(Player $player): void {
        $player->getInventory()->clearAll();
        $stick = VanillaItems::STICK();
        $stick->setCustomName("§7» §6Sumo Stick §7«");
        $player->getInventory()->setItem(0, $stick);
    
        $player->sendMessage(TextFormat::GREEN . "You have received the Sumo kit!");
    }

}
