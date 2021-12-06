<?php

namespace App\Controller;

use App\Service\FilesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{

    #[Route('/posts')]
    public function index(FilesService $filesService): Response
    {
        $posts = $filesService->getPostSummaries($this->getParameter('kernel.project_dir') . '/content');
        $first_post = array_shift($posts);
        return $this->render('posts/index.html.twig', [
          'first_post' => $first_post,
          'rest_posts' => $posts,
        ]);
    }

    #[Route('/posts/{slug}', name: 'markdown_reader')]
    public function show(string $slug, FilesService $fileService): Response
    {
        $contents = $fileService->getFileContents($this->getParameter('kernel.project_dir') . '/content/' . $slug . '/index.md');
        $meta = $fileService->parseJSON($this->getParameter('kernel.project_dir') . '/content/' . $slug . '/meta.json');
        $html = $fileService->parseMarkdown($contents);
        return $this->render('posts/show.html.twig', [
          'slug' => $slug,
          'content' => $html,
          'meta' => $meta,
        ]);
    }
}
