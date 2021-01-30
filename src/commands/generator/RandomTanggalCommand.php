<?php

namespace app\commands\generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RandomTanggalCommand extends Command
{
    protected static $defaultName = 'generator:random-tanggal';

    protected function configure()
    {
        $this
            ->setDescription('Generate Random date in Indonesia format between two date.')
            ->setHelp('This command allows you to generate random date in Indonesia format.')
            ->addArgument('from', InputArgument::REQUIRED, 'from date (yyyy-mm-dd).')
            ->addArgument('to', InputArgument::REQUIRED, 'to date (yyyy-mm-dd).')
            ->addArgument('number', InputArgument::REQUIRED, 'number date in Indonesia format to be generated.');
            // ->addOption(
            //     'src',
            //     null,
            //     InputOption::VALUE_REQUIRED,
            //     'source path of xlsx file',
            // );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');
        $number = $input->getArgument('number', 1);

        if ($from && $to) {
            $dtFrom = strtotime($from . " 00:00:00");
            $dtTo = strtotime($to . " 00:00:00");

            for ($i = 1; $i <= $number; $i++) {
                $int = rand($dtFrom, $dtTo);

                echo $this->getFullDate($int) . "\n";
            }
        }


        return Command::SUCCESS;
    }

    function getFullDate($time = 0)
    {
        $bulan =  ["" => "-",1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        // if ($time == "") {
        //     return $time;
        // }
        // if ($time && $time != "0000-00-00") {
        //     $time = strtotime($time);
        // } else {
        //     return "-";
        // }
        return date("j", $time) . " " . $bulan[date("n", $time)] . " " . date("Y", $time);
    }
}
