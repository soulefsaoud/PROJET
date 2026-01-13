<?php


namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(
    name: 'app:test-image-scraping',
    description: 'Test le scraping d\'image pour une URL'
)]
class TestImageScrapingCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('url', InputArgument::REQUIRED, 'URL à tester');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        $client = new Client([
            'timeout' => 30,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']
        ]);

        try {
            $io->title('Test de scraping d\'image pour : ' . $url);

            $response = $client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            // 1. Chercher JSON-LD
            $io->section('1. Recherche JSON-LD');
            $scriptTags = $crawler->filter('script[type="application/ld+json"]');
            if ($scriptTags->count() > 0) {
                foreach ($scriptTags as $script) {
                    $data = json_decode($script->textContent, true);
                    if (isset($data['@type']) && $data['@type'] === 'Recipe') {
                        if (isset($data['image'])) {
                            $io->success('Image trouvée dans JSON-LD : ' . json_encode($data['image']));
                        } else {
                            $io->warning('JSON-LD Recipe trouvé mais pas d\'image');
                        }
                    }
                }
            } else {
                $io->warning('Pas de JSON-LD trouvé');
            }

            // 2. Chercher meta tags
            $io->section('2. Recherche dans les meta tags');
            $metaSelectors = [
                'meta[property="og:image"]',
                'meta[name="twitter:image"]',
                'link[rel="image_src"]'
            ];

            foreach ($metaSelectors as $selector) {
                $element = $crawler->filter($selector)->first();
                if ($element->count() > 0) {
                    $imageUrl = $element->attr('content') ?: $element->attr('href');
                    $io->success("$selector : $imageUrl");
                    return Command::SUCCESS;
                }
            }
            $io->warning('Pas d\'image dans les meta tags');

            // 3. Lister TOUTES les images de la page
            $io->section('3. Toutes les images trouvées sur la page');
            $allImages = $crawler->filter('img');
            $io->text("Nombre total d'images : " . $allImages->count());

            $imageList = [];
            $allImages->each(function (Crawler $img, $i) use (&$imageList) {
                $src = $img->attr('src') ?: $img->attr('data-src') ?: $img->attr('data-lazy-src');
                $alt = $img->attr('alt') ?: 'pas d\'alt';
                $class = $img->attr('class') ?: 'pas de class';

                if ($src) {
                    $imageList[] = [
                        'index' => $i,
                        'src' => substr($src, 0, 80),
                        'alt' => substr($alt, 0, 40),
                        'class' => substr($class, 0, 40)
                    ];
                }
            });

            if (!empty($imageList)) {
                $io->table(['#', 'Source', 'Alt', 'Class'], $imageList);
                $io->info('Regardez les classes CSS des images principales pour améliorer les sélecteurs');
            } else {
                $io->error('Aucune image trouvée !');
            }

        } catch (\Exception $e) {
            $io->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
