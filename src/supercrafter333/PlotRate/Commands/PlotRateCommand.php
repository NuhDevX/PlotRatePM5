<?php

namespace supercrafter333\PlotRate\Commands;

use MyPlot\MyPlot;
use MyPlot\Plot;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\PlotRate\PlotRate;

/**
 * Class PlotRateCommand
 * @package supercrafter333\PlotRate\Commands
 */
class PlotRateCommand extends Command
{

    /**
     * PlotRateCommand constructor.
     * @param string $name
     * @param string $description
     * @param string $usageMessage
     * @param array|string[] $aliases
     */
    public function __construct()
    {
        parent::__construct("pr", "plotrate command");
        $this->setPermission("plotrate.cmd");
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = PlotRate::getInstance();
        $cfg = $pl->getConfig();
        if (empty($args[0])) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if (!$this->testPermission($s)) return;
        $subCmd = array_shift($args);
        switch ($subCmd) {
            case "help":
                $s->sendMessage("§eHelp list of PlotRate: \n");
                foreach ($this->subCmds as $subCmd => $desc) {
                    $s->sendMessage("§7/plotrate $subCmd §b- §8$desc");
                }
                $s->sendMessage("\n§e-------------------------");
                break;
            case "a":
            case "rand":
            case "random":
                if (!$s instanceof Player) {
                    $s->sendMessage($cfg->get("only-IG"));
                    return;
                }
                $matches = [];
                foreach ($pl->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $plot = MyPlot::getInstance()->getPlotByPosition($onlinePlayer);
                    if ($plot !== null) {
                        if ($plot->owner === $onlinePlayer->getName())
                        $matches[] = $onlinePlayer->getName();
                    }
                }
                $matchCount = count($matches, COUNT_RECURSIVE);
                if ($matchCount <= 0) {
                    $s->sendMessage($cfg->get("pr-a-noMatches"));
                    return;
                }
                $XplayerName = array_rand($matches);
                $playerName = $matches[$XplayerName];
                $player = $pl->getServer()->getPlayer($playerName);
                $plot = MyPlot::getInstance()->getPlotByPosition($player);
                $s->teleport($player);
                $s->sendMessage(str_replace(["{plot}", "{owner}"], [(string)$plot->X . ';' . (string)$plot->Z, $plot->owner], $cfg->get("pr-a-success")));
                return;
                break;
            case "info":
                $s->sendMessage("§eInformations of PlotRate: §r\n\n§7Made by: §rsupercrafter333\n§7Update by : §rNuhDev§7Helpers: §r---\n§7Icon by: §rShxGux\n§7License: §rApache 2.0 License\n§7GitHub: §rhttps://github.com/supercrafter333/PlotRate\n§7Poggit: §rhttps://poggit.pmmp.io/p/PlotRate\n\n§e-----------------------");
                return;
                break;
            case "editrating":
            case "er":
            if (empty($args[0])) {
            $s->sendMessage("§4Use: §r/pr editrating <rating: 0-5>");
            return;
        }
        if ((int)$args[0] < 0 || (int)$args[0] > 5) {
            $s->sendMessage("§4Use: §r/pr editrating <rating: 0-5>");
            return;
        }
        $plot = MyPlot::getInstance()->getPlotByPosition($s);
        if ($plot instanceof Plot) {
            PlotRate::getInstance()->ratePlot($plot, (int)$args[0]);
            $s->sendMessage(str_replace("{rating}", (string)$args[0], PlotRate::getInstance()->getConfig()->get("rating-edited")));
            return;
        } else {
            $s->sendMessage(PlotRate::getInstance()->getConfig()->get("not-in-plot"));
            return;
           }
        break;

        case "rate":
        if (empty($args[0])) {
            $s->sendMessage("§4Use: §r/pr rate <rating: 0-5>");
            return true;
        }
        if ((int)$args[0] < 0 || (int)$args[0] > 5) {
            $s->sendMessage("§4Use: §r/pr rate <rating: 0-5>");
            return true;
        }
        $plot = MyPlot::getInstance()->getPlotByPosition($s);
        if ($plot instanceof Plot) {
            PlotRate::getInstance()->ratePlot($plot, (int)$args[0]);
            $s->sendMessage(str_replace("{rating}", (string)$args[0], PlotRate::getInstance()->getConfig()->get("rated")));
        } else {
            $s->sendMessage(PlotRate::getInstance()->getConfig()->get("not-in-plot"));
        }
        return;
        break;
        
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return PlotRate::getInstance();
    }
}
