<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor.contact.create')]
    public function create(Request $request, ContactRepository $contactRepository): Response
    {   
        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // ContactRepositoty insertion request
            $contactRepository->save($contact, true);

            // Send email via SendEmailService

            // Generate flash message
            $this->addFlash("success", "Ce contact a été ajoutée avec succès");

            //Redirect to same page visitor.contact.create
            return $this->redirectToRoute("visitor.contact.create");
        }

        

        return $this->render('pages/visitor/contact/create.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
