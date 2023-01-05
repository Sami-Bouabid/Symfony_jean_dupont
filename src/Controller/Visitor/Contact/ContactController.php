<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor.contact.create')]
    public function create(Request $request): Response
    {   
        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // ContactRepositoty insertion request

            // Send email via SendEmailService

            // Generate flash message

            //Redirect to same page visitor.contact.create
        }

        

        return $this->render('pages/visitor/contact/create.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
