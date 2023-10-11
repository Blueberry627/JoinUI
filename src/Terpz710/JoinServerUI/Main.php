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

class Main extends PluginBase implements Listener {

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

        $this->openForm($player, $config);
    }

    public function openForm(Player $player, array $config) {
        $form = new SimpleForm(function (Player $player, $data) use ($config) {
            if ($data !== null) {
                if ($config["buttons"][$data] === "Yes") {
                    $this->playPopSound($player);
                    $this->sendTitle($player, $config["title_on_click"]);
                    $this->sendSubtitle($player, $config["subtitle_text"]);
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
    }

    public function playPopSound(Player $player) {
        $pk = new PlaySoundPacket();
        $pk->soundName = "random.pop";
        $location = $player->getLocation();
        $pk->x = $location->getX();
        $pk->y = $location->getY();
        $pk->z = $location->getZ();
        $pk->volume = 1.0;
        $pk->pitch = 1.0;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function sendTitle(Player $player, string $titleText) {
        $player->sendTitle(TextFormat::colorize($titleText), "");
    }

    public function sendSubtitle(Player $player, string $subtitleText) {
        $player->sendSubTitle(TextFormat::colorize($subtitleText));
    }

    public function onPlayerCloseForm(Player $player, ?SimpleForm $form) {
        if ($form !== null) {
            $config = $this->getConfig()->get("messages");
            $this->openForm($player, $config);
        }
    }
}
