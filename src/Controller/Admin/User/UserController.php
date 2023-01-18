<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\EditUserRoleFormType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/admin/user/list', name: 'admin.user.index')]
    public function index(UserRepository $userRepository): Response
    {   
        $users = $userRepository->findAll();
        return $this->render('pages/admin/user/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/user/{id<\d+>}/edit-role', name: 'admin.user.edit_role')]
    public function editRole(User $user, Request $request, UserRepository $userRepository): Response
    {   
        $form = $this->createForm(EditUserRoleFormType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $userRepository->save($user, true);
            $this->addFlash("success", "Les rôles de " . $user->getFirstName() . " " . $user->getLastName() . " ont été modifiés");
            return $this->redirectToRoute("admin.user.index");
        }
         
        return $this->render("pages/admin/user/edit_role.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    #[Route('/admin/user/{id<\d+>}/delete', name: 'admin.user.delete', methods: ['POST'])]
    public function delete(User $user, Request $request, UserRepository $userRepository, PostRepository $postRepository): Response
    {   
        if ( $this->isCsrfTokenValid('user_' . $user->getId(), $request->request->get('_csrf_token')) ) 
        {   
            // $posts = $postRepository->findAll();
            $posts = $postRepository->findBy(['user' => $user->getId()]);

            foreach ($posts as $post) 
            {
                $post->setAuthor(null);
            }

            $this->container->get("security.token_storage")->setToken(null);

            $userRepository->remove($user, true);
            $this->addFlash('success', "L'utilisateur " . $user->getFirstName() . " " . $user->getLastName() . " a été supprimé.");
        }

        return $this->redirectToRoute('admin.user.index');
    }
}
