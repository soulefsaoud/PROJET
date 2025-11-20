<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

class ImageDownloadService
{
    private Client $httpClient;
    private SluggerInterface $slugger;
    private Filesystem $filesystem;
    private LoggerInterface $logger;
    private string $uploadDir;

    public function __construct(
        SluggerInterface $slugger,
        LoggerInterface $logger,
        string $uploadDir = 'public/uploads/recipes'
    ) {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; RecipeBot/1.0)'
            ]
        ]);
        $this->slugger = $slugger;
        $this->filesystem = new Filesystem();
        $this->logger = $logger;
        $this->uploadDir = $uploadDir;

        // Créer le dossier s'il n'existe pas
        if (!$this->filesystem->exists($this->uploadDir)) {
            $this->filesystem->mkdir($this->uploadDir, 0755);
        }
    }

    public function downloadImage(string $imageUrl, string $recipeName): ?string
    {
        try {
            // Nettoyer l'URL (parfois relatives)
            $imageUrl = $this->normalizeImageUrl($imageUrl);

            if (!$this->isValidImageUrl($imageUrl)) {
                return null;
            }

            $response = $this->httpClient->get($imageUrl);
            $imageContent = $response->getBody()->getContents();

            if (empty($imageContent)) {
                return null;
            }

            // Générer un nom de fichier unique
            $filename = $this->generateFilename($imageUrl, $recipeName);
            $filePath = $this->uploadDir . '/' . $filename;

            // Sauvegarder l'image
            $this->filesystem->dumpFile($filePath, $imageContent);

            $this->logger->info('Image téléchargée avec succès', [
                'original_url' => $imageUrl,
                'saved_path' => $filePath,
                'recipe' => $recipeName
            ]);

            // Retourner le chemin relatif pour la base de données
            return 'uploads/recipes/' . $filename;

        } catch (\Exception $e) {
            $this->logger->error('Erreur téléchargement image', [
                'url' => $imageUrl,
                'error' => $e->getMessage(),
                'recipe' => $recipeName
            ]);
            return null;
        }
    }

    private function normalizeImageUrl(string $url): string
    {
        // Si l'URL est relative, on ne peut pas la traiter sans le domaine de base
        if (strpos($url, 'http') !== 0) {
            // Dans ce cas, il faudrait passer le domaine de base depuis le scraper
            return $url;
        }

        return $url;
    }

    private function isValidImageUrl(string $url): bool
    {
        // Vérifications basiques
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Vérifier l'extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return in_array($extension, $allowedExtensions);
    }

    private function generateFilename(string $imageUrl, string $recipeName): string
    {
        // Extraire l'extension
        $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (!$extension) {
            $extension = 'jpg'; // Extension par défaut
        }

        // Créer un nom basé sur le nom de la recette
        $baseFilename = $this->slugger->slug($recipeName)->lower();

        // Ajouter un timestamp pour éviter les collisions
        $timestamp = time();

        return $baseFilename . '_' . $timestamp . '.' . $extension;
    }

    public function getImageInfo(string $imagePath): ?array
    {
        $fullPath = $this->uploadDir . '/' . basename($imagePath);

        if (!$this->filesystem->exists($fullPath)) {
            return null;
        }

        $imageInfo = getimagesize($fullPath);
        if (!$imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime'],
            'size' => filesize($fullPath)
        ];
    }
}
