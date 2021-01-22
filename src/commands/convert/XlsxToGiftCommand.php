<?php

namespace app\commands\convert;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxToGiftCommand extends Command
{
    protected static $defaultName = 'convert:xlsx-to-gift';

    protected function configure()
    {
        $this
            ->setDescription('Convert xlsx file to moodle GiftFormat.')
            ->setHelp('This command allows you to convert a xlsx file to Moodle GiftFormat file.')
            ->addArgument('src', InputArgument::REQUIRED, 'source path of xlsx file.');
            // ->addOption(
            //     'src',
            //     null,
            //     InputOption::VALUE_REQUIRED,
            //     'source path of xlsx file',
            // );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getArgument('src'));

        if ($path) {
            $spreadsheet = IOFactory::load($path);

            $worksheet = $spreadsheet->getActiveSheet();

            $rows = [];

            $result = "";

            foreach ($worksheet->getRowIterator() as $row) {
                if ($row->getRowIndex() == 1) {
                    continue;
                }
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops through all cells,

                $res = "";
                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    switch ($column) {
                        case "A":
                            $res .= "::" . $cell->getValue() . "::";
                            break;
                        case "B":
                            $val = $this->cleanString($cell->getValue());

                            $res .= "[html]<p>" . $val . "</p> {\n";
                            break;
                        default:
                            $bgColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                            if ($bgColor == "00FA00") {
                                $res .= "=";
                            } else {
                                $res .= "~";
                            }

                            $val = $this->cleanString($cell->getValue());

                            $res .= "<p>" . $val . "</p>\n";
                            break;
                    }
                }
                $res .= $res ? "}" : "";

                $result .= $res . "\n";
            }

            $fileInfo = pathinfo($path);
            $destPath = $fileInfo["dirname"] . "/" . $fileInfo["filename"] . ".gift";

            file_put_contents($destPath, $result);
        }


        return Command::SUCCESS;
    }

    public function cleanString($str)
    {
        $str = trim($str);
        $str = str_replace('src="', 'src="@@PLUGINFILE@@/', $str);
        $str = str_replace('=', '\=', $str);
        $str = nl2br($str);

        return $str;
    }
}
