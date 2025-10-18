<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Form\MDPFormType;
use App\Repository\UtilisateurRepository;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            $ruta = in_array('ROLE_DENTISTE', $this->getUser()->getRoles()) ? 'app_dentiste' : 'app_profile';
            $ruta = in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'app_dashboard' : $ruta;
            return $this->redirectToRoute($ruta, [], Response::HTTP_SEE_OTHER);
        }

        $error2 = null;
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($request->getSession()->get('error')) {
            $error2 = $request->getSession()->get('error');
            $request->getSession()->remove('error');
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'error2' => $error2,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/changer_mot_de_pass', name: 'app_mdp')]
    public function mdp(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(MDPFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('security/mdp.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/reinitialiser_mot_de_pass', name: 'app_reinitialiser_mdp')]
    public function reinitialiser_mdp(Request $request, UserPasswordHasherInterface $userPasswordHasher, UtilisateurRepository $utilisateurRepository): Response
    {
        if ($this->getUser()) {
            $ruta = in_array('ROLE_DENTISTE', $this->getUser()->getRoles()) ? 'app_dentiste' : 'app_profile';
            $ruta = in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'app_dashboard' : $ruta;
            return $this->redirectToRoute($ruta, [], Response::HTTP_SEE_OTHER);
        }

        if ($request->request->get('email') && $request->request->get('password') && $request->request->get('code') == '123456') {
            $user = $utilisateurRepository->findOneBy(['email' => $request->request->get('email')]);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )

            );
            $request->getSession()->set('error', 'Réinitialisation du mot de passe réussie, veuillez vous authentifier.');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('security/reinitialiser_mdp.html.twig', [

        ]);
    }

    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/compare-password', name: 'compare_password')]
    public function comparePassword(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $plainPassword = $request->request->get('password');

        if ($user instanceof UserInterface) {
            $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $plainPassword);
            $mensaje = $isPasswordValid ? '1' : '0';
        }
        $response = [
            'mensaje' => $mensaje,
        ];

        return new JsonResponse($response);
    }

    #[Route('/compare_email', name: 'compare_email')]
    public function compareemail(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $email = $request->request->get('email');
        $mensaje = $utilisateurRepository->findBy(['email' => $email]) ? '1' : '0';
        $response = [
            'mensaje' => $mensaje,
        ];

        return new JsonResponse($response);
    }
}
