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
            ->addArgument('src', InputArgument::REQUIRED, 'source path of xlsx file.')
            ->addArgument('kode', InputArgument::OPTIONAL, 'question code.');
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
        $kode = $input->getArgument('kode');

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
                $cat = false;
                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    switch ($column) {
                        case "A":
                            if ($cell->getValue() == "CAT") {
                                $res .= "\n\$CATEGORY: " . $worksheet->getCell("B" . $row->getRowIndex())->getValue();
                                $cat = true;
                                break 2;
                            } else {
                                $res .= "::" . strtoupper($kode) . str_pad($cell->getValue(), 3, '0', STR_PAD_LEFT) . "::";
                            }
                            break;
                        case "B":
                            $val = $this->cleanString($cell->getValue());

                            $res .= "[html]<p>" . $val . "</p> {\n";
                            break;
                        default:
                            $bgColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                            if ($bgColor == "00FF00") {
                                $res .= "=";
                            } else {
                                $res .= "~";
                            }

                            $val = $this->cleanString($cell->getValue());

                            $res .= "<p>" . $val . "</p>\n";
                            break;
                    }
                }
                if ($cat) {
                    $res .= $res ? "\n" : "";
                } else {
                    $res .= $res ? "}\n" : "";
                }

                $result .= $res . "\n";
            }

            $fileInfo = pathinfo($path);
            $destPath = $fileInfo["dirname"] . "/gift_" . $fileInfo["filename"] . ".txt";

            file_put_contents($destPath, $result);
        }


        return Command::SUCCESS;
    }

    public function cleanString($str)
    {
        $str = trim($str);
        $str = str_replace('src="', 'src="@@PLUGINFILE@@/', $str);
        $str = str_replace('=', '\=', $str);
        $str = str_replace('~', '\~', $str);
        $str = str_replace('#', '\#', $str);
        $str = str_replace('{', '\{', $str);
        $str = str_replace('}', '\}', $str);
        $str = str_replace(':', '\:', $str);
        $str = nl2br($str);

        return $str;
    }
}
