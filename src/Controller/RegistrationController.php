<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
// NOT IN REGISTRATION TEMPLATE - START
use App\Repository\UserRepository;
// NOT IN REGISTRATION TEMPLATE - END
use App\Security\LoginFormAuthenticator;
// NOT IN REGISTRATION TEMPLATE - START
use SymfonyCasts\Bundle\VerifyUser\Controller\VerifyUserControllerTrait;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelperInterface;
// NOT IN REGISTRATION TEMPLATE - END
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    // NOT IN REGISTRATION TEMPLATE - START

    use VerifyUserControllerTrait;

    /**
     * @var VerifyHelperInterface
     */
    private $helper;

    public function __construct(VerifyHelperInterface $helper)
    {
        $this->helper = $helper;
    }
    // NOT IN REGISTRATION TEMPLATE - END

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            // NOT IN REGISTRATION TEMPLATE - START
            $signature = $this->getSignature($user->getId());

            // URI to be used in email template
            $uri = $this->generateUrl('app_validate_user', ['token' => $signature]);

            //@TODO send email here
            //@TODO remove flash, used for dev purposes only
            $this->addFlash('success', $uri);

            // NOT IN REGISTRATION TEMPLATE - END

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // NOT IN REGISTRATION TEMPLATE - START
    /**
     * @Route("/verify/{token}", name="app_validate_user")
     */
    public function verifyUserEmail(string $token): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$this->helper->isValidSignature($token, $user->getId())) {
            throw new \Exception("Invalid signature.");
        }

        if (true === $user->isVerified()) {
            $this->addFlash('success', 'You\'ve already been verified.');
            return $this->redirectToRoute('app_register');
        }

        /** @var UserRepository $repo */
        $repo = $this->getDoctrine()->getRepository(User::class);
        $repo->markAsVerifiedUser($user);

        $this->addFlash('success', 'Your e-mail address has been verified.');

        return $this->redirectToRoute('app_register');
    }
    // NOT IN REGISTRATION TEMPLATE - END
}
