<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'list_post')]
    public function index(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $allCat = $categoryRepository->findAll();
        $posts = $postRepository->findBy([],['created_at'=>'DESC']);
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'cats' => $allCat
        ]);
    }

    #[Route('/post/new',name:'new_post')]
    #[Isgranted('ROLE_EDITOR')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        //création d'un post
        $post = new Post();

        //hydratation de ce qui n'est pas géré
        $post->setUser($this->getUser());
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setUpdatedAt(new \DateTimeImmutable());
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Le post a bien été créé');
            return $this->redirectToRoute('list_post');
        }

        return $this->renderForm('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/{id<\d+>}',name:'post_detail')]
    public function detail(Post $post=null, Request $request, EntityManagerInterface $entityManager)
    {
        if($post==null) {
            $this->addFlash('danger', 'Pas sûr que ça soit un post');
            return $this->redirectToRoute('list_post');
        }
        //création d'un comment
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUpdatedAt(new \DateTimeImmutable());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Le comment a bien été créé');
            return $this->redirect('/post/' . $post->getId());
        }

        return $this->render('post/detail.html.twig', [
            "post" => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/post/delete/{id<\d+>}',name:'post_delete')]
    #[Security("is_granted('ROLE_ADMIN') or post.getUser() == user")]
    public function supprimer(Post $post = null, EntityManagerInterface $entityManager): Response
    {

        if($post==null) {
            $this->addFlash('danger', 'Pas sûr que ça soit un post');
            return $this->redirectToRoute('list_post');
        }

            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Le post a bien été supprimé.');

        return $this->redirectToRoute("list_post");
    }

    #[Route('/post/update/{id<\d+>}',name:'post_update')]
    #[Security("is_granted('ROLE_ADMIN') or post.getUser() == user")]
    public function update(Post $post = null, EntityManagerInterface $entityManager, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Le post a bien été modifié.');
            return $this->redirectToRoute('list_post');
        }
        return $this->render
        ('post/update.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('post/category/{name}',name:'postBy_cat')]
    public function postByCat(Category $category, PostRepository $postRepository, CategoryRepository $categoryRepository){

        if($category==null) {
            $this->addFlash('danger', 'Pas sûr que ça soit une catégorie');
            return $this->redirectToRoute('list_post');
        }

        $allCat = $categoryRepository->findAll();
        $postByCats= $category->getPosts();

        return $this->render('post/list.html.twig', [
            'posts' => $postByCats,
            'cats' => $allCat,
            'searchCatName' => $category->getName()
        ]);
    }

    #[Route('post/date/{date}',name:'postBy_date')]
    public function postByDate(String $date, PostRepository $postRepository, CategoryRepository $categoryRepository){

        $from = \DateTime::createFromFormat('d-m-Y',$date)->format("Y-m-d");
        $to = \DateTime::createFromFormat('d-m-Y',$date)->modify('+1 day')->format('Y-m-d');
        $allCat = $categoryRepository->findAll();
        $postByDate= $postRepository->findByDates($from, $to);

        return $this->render('post/list.html.twig', [
            'posts' => $postByDate,
            'cats' => $allCat
        ]);
    }

    #[Route('/search',name:'search')]
    public function search(Request $request, PostRepository $postRepository, CategoryRepository $categoryRepository)
    {
        $search = $request->query->get("search");
        $allCat = $categoryRepository->findAll();
        $postSearch = $postRepository->FindBySearch($search);

        return $this->render('post/list.html.twig', [
            'posts' => $postSearch,
            'cats' => $allCat
        ]);
    }
}
