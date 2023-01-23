<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Post;
use App\Entity\Category;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'visitor.blog.index')]
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository): Response
    {   
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $posts = $postRepository->findBy(['isPublished' => true]);

        return $this->render('pages/visitor/blog/index.html.twig', compact('categories', 'tags', 'posts'));
    }

    #[Route('/blog/post/{id<\d+>}/{slug}', name: 'visitor.blog.post.show')]
    public function show(Post $post): Response
    {   
        
        return $this->render('pages/visitor/blog/show.html.twig', compact('post'));
    }

    #[Route('/blog/post/filter_by_category/{id<\d+>}/{slug}', name: 'visitor.blog.posts.filter_by_category')]
    public function filterByCategory(Category $category, CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository): Response
    {   
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        // $posts = $category->getPosts();
        $posts = $postRepository->filterPostsByCategory($category->getId());

       

        return $this->render('pages/visitor/blog/index.html.twig', compact('categories', 'tags', 'posts'));
    }
}
