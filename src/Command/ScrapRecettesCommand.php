<?php

namespace App\Command;

use App\Services\RecetteScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scrap-recipes',
    description: 'Scrape recipes from URLs'
)]
class ScrapRecettesCommand extends Command
{
    private RecetteScraperService $scraperService;

    public function __construct(RecetteScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('urls', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'URLs to scrape');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $urls = $input->getArgument('urls');

        $io->title('Scraping des recettes');

        $results = $this->scraperService->scrapeMultipleRecipes($urls);

        foreach ($results as $result) {
            if ($result['success']) {
                $io->success("✓ " . $result['recipe']->getNom() . " depuis " . $result['url']);
            } else {
                $io->error("✗ Échec pour " . $result['url']);
            }
        }

        return Command::SUCCESS;
    }
}
