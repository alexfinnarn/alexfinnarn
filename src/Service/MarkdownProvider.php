<?php

namespace App\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

class MarkdownProvider
{

    /**
     * @var \League\CommonMark\MarkdownConverter
     */
    private MarkdownConverter $parser;

    public function __construct(Environment $environment)
    {
        // Configure the Environment with all the CommonMark parsers/renderers
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js', 'shell']));
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());

        // Add the extension
        $environment->addExtension(new FrontMatterExtension());

        // Instantiate the converter engine and start converting some Markdown!
        $this->parser = new MarkdownConverter($environment);
    }

    public function parse(false|string $contents): \League\CommonMark\Output\RenderedContentInterface
    {
        return $this->parser->convertToHtml($contents);
    }

    public function getMeta($contents): array
    {
        $meta = [];
        if ($contents instanceof RenderedContentWithFrontMatter) {
            $meta = $contents->getFrontMatter();
        }
        return $meta;
    }
}
