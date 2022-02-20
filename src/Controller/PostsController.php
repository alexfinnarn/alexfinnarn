<?php

namespace App\Controller;

use App\Service\MarkdownProvider;
use JetBrains\PhpStorm\Pure;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{

    #[Route('/posts')]
    public function index(MarkdownProvider $markdownProvider, Finder $finder): Response
    {
        $posts = $this->getPosts($markdownProvider, $finder);
        $first_post = array_shift($posts);
        return $this->render('posts/index.html.twig', [
          'first_post' => $first_post,
          'rest_posts' => $posts,
        ]);
    }

    #[Route('/posts/{slug}', name: 'markdown_reader')]
    public function show(MarkdownProvider $markdownProvider, string $slug): Response
    {
        $html = $this->getPostHTML($markdownProvider, $slug);
        $meta = $this->getMetaFromHTML($html);
        return $this->render('posts/show.html.twig', [
          'slug' => $slug,
          'content' => $html,
          'meta' => $meta,
        ]);
    }

    /**
     * @param \Symfony\Component\Finder\Finder $finder
     * @param \App\Service\MarkdownProvider $markdownProvider
     *
     * @return array
     */
    public function getPosts(MarkdownProvider $markdownProvider, Finder $finder): array
    {
        $posts = [];
        foreach ($finder->in($this->getParameter('kernel.project_dir') . '/content')->files() as $file) {
            $content = file_get_contents($file->getPathname());
            $html = $markdownProvider->parse($content);
            $meta = [];
            if ($html instanceof RenderedContentWithFrontMatter) {
                $meta = $html->getFrontMatter();
            }
            $posts[] = $meta;
        }
        return $posts;
    }

    private function getPostHTML(MarkdownProvider $markdownProvider, string $slug): \League\CommonMark\Output\RenderedContentInterface
    {
        $contents = file_get_contents($this->getParameter('kernel.project_dir') . '/content/' . $slug . '.md');
        return $markdownProvider->parse($contents);
    }

    #[Pure]
    private function getMetaFromHTML(\League\CommonMark\Output\RenderedContentInterface $html)
    {
        return $html instanceof RenderedContentWithFrontMatter ? $html->getFrontMatter() : null;
    }
}
