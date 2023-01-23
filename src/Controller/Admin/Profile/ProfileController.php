<?php

namespace App\Controller\Admin\Profile;

use App\Form\EditProfileFormType;
use App\Repository\UserRepository;
use App\Form\ChangePasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/admin/profile', name: 'admin.profile.index')]
    public function index(): Response
    {
        return $this->render('pages/admin/profile/index.html.twig');
    }
    
    #[Route('/admin/profile/edit', name: 'admin.profile.edit')]
    public function edit(Request $request, UserRepository $userRepository): Response
    {   
        $user = $this->getUser();
        $form = $this->createForm(EditProfileFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $userRepository->save($user, true);
            $this->addFlash('success', "Votre profil a été modifié");
            return $this->redirectToRoute("admin.profile.index");
        }

        return $this->render('pages/admin/profile/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/profile/edit_password', name: 'admin.profile.edit_password')]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, UserRepository $userRepository): Response
    {   
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class, null, ["required_current_password" => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
           $password_hashed =  $hasher->hashPassword($user, $form->get('plainPassword')->getData());
           $user->setPassword($password_hashed);

           $userRepository->save($user, true);

           $this->addFlash('success', "Votre mot de passe a été changé");
           return $this->redirectToRoute("admin.profile.index");
        }

        return $this->render('pages/admin/profile/edit_password.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
