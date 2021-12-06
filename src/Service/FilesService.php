<?php

namespace App\Service;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\Finder\Finder;

class FilesService
{

    public function __construct()
    {
        $this->finder = new Finder();
        $this->parser = new GithubFlavoredMarkdownConverter();
    }

    public function parseMarkdown(false|string $contents)
    {
        $this->parser->convertToHtml($contents);
    }

    public function parseJSON(string $fileName): mixed
    {
        $contents = json_decode($this->getFileContents($fileName));
        // @todo Do something with dates?
        return $contents;
    }

    public function getFileContents(string $fileName): false|string
    {
        return file_get_contents($fileName);
    }

    public function getPostSummaries(string $directoryName): array
    {
        $content = [];
        foreach ($this->finder->in($directoryName)->files() as $file) {
            if ($file->getFilename() == 'meta.json') {
                $json = $this->parseJSON($file->getPathname());
                $content[] = $json;
            }
        }

        return $content;
    }
}
