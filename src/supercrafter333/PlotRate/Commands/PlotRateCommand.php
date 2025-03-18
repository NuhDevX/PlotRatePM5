<?php

namespace supercrafter333\PlotRate\Commands;

use MyPlot\MyPlot;
use MyPlot\Plot;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\PlotRate\PlotRate;

class PlotRateCommand extends Command
{
    private array $subCmds = [
        "help" => "Menampilkan daftar perintah",
        "a" => "Teleport ke plot acak",
        "info" => "Menampilkan informasi plugin",
        "editrating" => "Edit rating plot",
        "rate" => "Memberikan rating plot",
        "unrate" => "Menghapus rating plot"
    ];

    public function __construct()
    {
        parent::__construct("pr", "PlotRate command");
        $this->setPermission("plotrate.cmd");
        $this->setUsage("/pr <subcommand>");
    }

    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($s)) return;

        if (empty($args[0])) {
            $s->sendMessage($this->getUsage());
            return;
        }

        $pl = PlotRate::getInstance();
        $cfg = $pl->getConfig();
        $subCmd = strtolower(array_shift($args));

        switch ($subCmd) {
            case "help":
                $s->sendMessage("§eHelp list of PlotRate: \n");
                foreach ($this->subCmds as $cmd => $desc) {
                    $s->sendMessage("§7/plotrate $cmd §b- §8$desc");
                }
                return;

            case "a":
            case "rand":
            case "random":
                if (!$s instanceof Player) {
                    $s->sendMessage($cfg->get("only-IG"));
                    return;
                }
                $matches = [];
                foreach ($pl->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $plot = MyPlot::getInstance()->getPlotByPosition($onlinePlayer->getPosition());
                    if ($plot !== null && $plot->owner === $onlinePlayer->getName()) {
                        $matches[] = $onlinePlayer->getName();
                    }
                }
                if (empty($matches)) {
                    $s->sendMessage($cfg->get("pr-a-noMatches"));
                    return;
                }
                $playerName = $matches[array_rand($matches)];
                $player = $pl->getServer()->getPlayerExact($playerName);

                if ($s instanceof Player && $player instanceof Player) {
                    $s->teleport($player->getPosition());
                    $plot = MyPlot::getInstance()->getPlotByPosition($player->getPosition());
                    $s->sendMessage(str_replace(["{plot}", "{owner}"], [$plot->X . ';' . $plot->Z, $plot->owner], $cfg->get("pr-a-success")));
                }
                return;

            case "info":
                $s->sendMessage("§eInformations of PlotRate:\n\n§7Made by: §rsupercrafter333\n§7Update by: §rNuhDev\n§7License: §rApache 2.0\n§7GitHub: §rhttps://github.com/supercrafter333/PlotRate\n\n§e-----------------------");
                return;

            case "editrating":
            case "er":
            case "rate":
                if (empty($args[0]) || (int)$args[0] < 0 || (int)$args[0] > 5) {
                    $s->sendMessage("§4Use: §r/pr {$subCmd} <rating: 0-5>");
                    return;
                }
                if ($s instanceof Player) {
                    $plot = MyPlot::getInstance()->getPlotByPosition($s->getPosition());
                    if ($plot instanceof Plot) {
                        PlotRate::getInstance()->ratePlot($plot, (int)$args[0]);
                        $s->sendMessage(str_replace("{rating}", (string)$args[0], $cfg->get("rated")));
                    } else {
                        $s->sendMessage($cfg->get("not-in-plot"));
                    }
                }
                return;

            case "unrate":
            case "ur":
                if ($s instanceof Player) {
                    $plot = MyPlot::getInstance()->getPlotByPosition($s->getPosition());
                    if ($plot instanceof Plot) {
                        if (!PlotRate::getInstance()->isRated($plot)) {
                            $s->sendMessage($cfg->get("not-rated"));
                            return;
                        }
                        PlotRate::getInstance()->unratePlot($plot);
                        $s->sendMessage($cfg->get("unrated"));
                    } else {
                        $s->sendMessage($cfg->get("not-in-plot"));
                    }
                }
                return;
        }
    }

    public function getPlugin(): Plugin
    {
        return PlotRate::getInstance();
    }
}
