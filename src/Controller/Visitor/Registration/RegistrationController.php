<?php
namespace App\Controller\Visitor\Registration;



use App\Entity\User;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'visitor.registration.register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        SendEmailService $sendEmailService
    ): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('visitor.welcome.index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
           // Generates security token to ckeck email confirmation
            $tokenGenerated = $tokenGenerator->generateToken();
            $user->setTokenForEmailVerification($tokenGenerated);
 
           // Generates a date limit for email verification
            $dealine = (new \DateTimeImmutable('now'))->add(new \DateInterval('P1D'));
            $user->setDeadLineForEmailVerification($dealine);
           
           // encode the plain password
            $passwordHashed = $userPasswordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($passwordHashed);

            // The entityManager prepares the sql request then executes it (flush());
            $entityManager->persist($user); 
            $entityManager->flush();

            // do anything else you need here, like send an email
            $sendEmailService->send([
                "sender_email" => "medecine-du-monde@gmail.com",
                "sender_name" => "Jean Dupont",
                "recipient_email" => $user->getEmail(),
                "subject" => "Verification de votre compte sur le blog de Jean Dupont",
                "html_template" => "email/email_verification.html.twig",
                "context" => [
                    "user_id" => $user->getId(),
                    "token_for_email_verification" => $user->getTokenForEmailVerification(),
                    "dead_line_for_email_verification" => $user->getDeadLineForEmailVerification()->format("d/m/Y à H:i:s")
                ]
            ]);

            return $this->redirectToRoute('visitor.registration.waiting_for_email_verification');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/waiting-for-email-verificaion', name: 'visitor.registration.waiting_for_email_verification')]
    public function waitingEmailVerification() : Response
    {
        return $this->render("pages/visitor/registration/waiting_for_email_verification.html.twig");
    }

    #[Route('/register/email-verification/{id<\d+>}/{token}', name: 'visitor.registration.email_verification')]
    public function emailVerification(User $user, string $token, UserRepository $userRepository) : Response
    {
        // If the user doesnt exists, we deny the access
        if (!$user)
        {
           throw new AccessDeniedException();
        }

        // If user already verified their account, we redirect them to the connection page "flash" 
        // messaging them they already can connect
        
        if ($user->isIsVerified()) 
        {   
            $this->addFlash("warning", "Votre compte à déja été verifié. Vous pouvez vous connecter");
            return $this->redirectToRoute("visitor.authentication.login");
        }

        // If token is wrong, null or doesnt match the token generated
        if ( empty($token) || ($user->getTokenForEmailVerification() === "") || ($user->getTokenForEmailVerification() === null)|| ($token !== $user->getTokenForEmailVerification()) )
        {
            throw new AccessDeniedException();
        }

        // If time for verify account expired
        if ( (new \DateTimeImmutable('now') > $user->getDeadLineForEmailVerification()) ) 
        {   
            $deadline = $user->getDeadLineForEmailVerification()->format("d/m/Y à H:i:s");
            $userRepository->remove($user, true);
            throw new CustomUserMessageAccountStatusException("Le delai de verification a expiré le : $deadline. Veuillez vous réinscrire.");
        }

        // We verify the account
        $user->setIsVerified(true);

        // Initializes account verification date
        $user->setVerifiedAt(new \DateTimeImmutable("now"));

        // We remove the safety token
        $user->setTokenForEmailVerification('');

        // Modify request on the entity $user
        $userRepository->save($user, true);

        // Generates the flash message
        $this->addFlash("success", "Votre compte a bien été vérifié.");

        // Redirect to home page
        return $this->redirectToRoute("visitor.authentication.login");

    }
}
