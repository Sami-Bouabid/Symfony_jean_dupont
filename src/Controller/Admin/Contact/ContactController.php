<?php

namespace App\Controller\Admin\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/admin/contact', name: 'admin.contact.index')]
    public function index(ContactRepository $contactRepository): Response
    {   
        $contacts = $contactRepository->findAll();
        return $this->render('pages/admin/contact/index.html.twig', compact('contacts'));
    }

    #[Route('/admin/contact/{id<\d+>}/show}', name: 'admin.contact.show')]
    public function show(Contact $contact): Response
    {   
        
        return $this->render('pages/admin/contact/show.html.twig', compact('contact'));
    }

    #[Route('/admin/contact/{id<\d+>}/delete', name: 'admin.contact.delete', methods: ['POST'])]
    public function delete(Contact $contact, ContactRepository $contactRepository, Request $request): Response
    {
        if ( $this->isCsrfTokenValid('contact_' . $contact->getId(), $request->request->get('_csrf_token')) ) 
        {
            $contactRepository->remove($contact, true);
            $this->addFlash('success', "Le contact " . $contact->getFirstName() . " " . $contact->getLastName() . " a été supprimé.");
        }

        return $this->redirectToRoute('admin.contact.index');
    }

}
