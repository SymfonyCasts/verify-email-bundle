<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
// NOT IN REGISTRATION TEMPLATE - START
use JRushlow\Bundle\VerifyUser\VerifierHelperInterface;
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
    /**
     * @var VerifierHelperInterface
     */
    private $helper;

    public function __construct(VerifierHelperInterface $helper)
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
            $expiresAt = (new \DateTimeImmutable('now'))
                ->modify(sprintf('+%d seconds', 3600))
            ;

            $signature = $this->helper->getSignature($user->getId(), $expiresAt)->getSignature();

            $uri = $this->generateUrl('app_validate_user', ['token' => $signature]);

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
        $userId = $this->getUser()->getId();

        $isValid = $this->helper->isValidSignature($token, $userId);

        if (!$isValid) {
            throw new \Exception("Invalid signature.");
        }

        $this->addFlash('success', 'Your e-mail address has been verified.');

        return $this->redirectToRoute('app_register');
    }
    // NOT IN REGISTRATION TEMPLATE - END
}
