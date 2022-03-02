<?php

namespace App\Command;

use App\Config;
use MatthiasMullie\Minify\CSS;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'setup:adminer',
    description: 'Download Adminer (Databasetool)',
)]
class SetupAdminerCommand extends Command
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $adminerUrl = Config::getAdminerDownloadUrl();
        $adminerThemeUrl = Config::getAdminerThemeDownloadUrl();

        $adminerPath = $this->kernel->getProjectDir() . '/adminer/';

        $css = "";
        if ($adminerThemeUrl !== '') {
            $minify = new CSS();
            if (!$css = file_get_contents($adminerThemeUrl)) {
                $io->error('Adminer could not be downloaded from ' . $adminerThemeUrl);

                return Command::FAILURE;
            }
            $minify->add($css);
            $css = $minify->minify();
            $css = htmlspecialchars($css);
            $css = "echo \"<style>\".htmlspecialchars_decode('$css').\"</style>\";";
        }

        if (!file_put_contents($adminerPath . 'adminer.php', file_get_contents($adminerUrl) . $css)) {
            $io->error('Adminer could not be downloaded from ' . $adminerUrl);

            return Command::FAILURE;
        }

        $io->success('Adminer has been downloaded and is now reachable with /adminer');

        return Command::SUCCESS;
    }
}