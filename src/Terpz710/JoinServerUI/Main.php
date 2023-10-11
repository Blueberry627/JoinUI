<?php

declare(strict_types=1);

namespace Terpz710\JoinServerUI;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; // Import the necessary packet

class Main extends PluginBase implements Listener {

    private array $waitingForConfirmation = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $this->sendJoinForm($player);
    }

    public function sendJoinForm(Player $player) {
        $config = $this->getConfig()->get("messages");
        $form = new SimpleForm(function (Player $player, $data) use ($config) {
            if (in_array($player->getName(), $this->waitingForConfirmation)) {
                if ($data === null) {
                    $this->playPopSound($player);
                    $this->sendTitle($player, $config["title_on_click"]);
                    $this->sendSubtitle($player, $config["subtitle_text"]);
                } else {
                    $player->sendMessage($config["must_click_ok_message"]);
                }
                $key = array_search($player->getName(), $this->waitingForConfirmation);
                if ($key !== false) {
                    unset($this->waitingForConfirmation[$key]);
                }
            }
        });

        $form->setTitle($config["title"]);
        $content = $config["content"];
        $form->setContent(implode("\n", $content));

        $buttons = $config["buttons"];
        foreach ($buttons as $button) {
            $form->addButton($button);
        }

        $player->sendForm($form);

        $this->waitingForConfirmation[] = $player->getName();
    }

    public function playPopSound(Player $player) {
        $pk = new LevelSoundEventPacket();
        $pk->sound = LevelSoundEventPacket::SOUND_RANDOM_POP;
        $pk->position = $player->getPosition();
        $pk->volume = 1.0;
        $pk->pitch = 1.0;
        $player->dataPacket($pk);
    }

    public function sendTitle(Player $player, string $titleText) {
        $player->sendTitle(TextFormat::colorize($titleText), "");
    }
    
    public function sendSubtitle(Player $player, string $subtitleText) {
        $player->sendSubTitle(TextFormat::colorize($subtitleText));
    }
}
