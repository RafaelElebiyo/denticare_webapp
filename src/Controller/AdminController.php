<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Rendezvous;
use App\Entity\Temoinage;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Repository\TemoinageRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RendezvousRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET', 'POST'])]
    public function dashboard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $formuser = $this->createForm(UtilisateurType::class, $user);
        $formuser->handleRequest($request);
        if ($formuser->isSubmitted() && $formuser->isValid()) {
            $entityManager->flush();
           
            /** @var UploadedFile $photoFile */
            $photoFile = $request->files->get('photo');
            if ($photoFile) {
                try {
                    $photoFile->move(
                        'assets/img/userimages/',
                        'photouser'.$user->getId().'.jpg'
                    );
                } catch (FileException $e) {
                    dd($e);
                }
            }
            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('admin/dashboard.html.twig', [
            'utilisateur' => $user,
            'formuser' => $formuser,
        ]);
    }

    #[Route('/activer', name: 'app_activer')]
    public function activer(Request $request,
    QuestionRepository $questionRepository,UtilisateurRepository $utilisateurRepository,
    ReponseRepository $reponseRepository, RendezvousRepository $rendezvousRepository,
    TemoinageRepository $temoinageRepository,EntityManagerInterface $entityManager): JsonResponse
    {   $num = $request->request->get('id');
        $tipo = $request->request->get('tipo');
        $tipos = [  'question'=> $questionRepository,
                    'reponse'=>$reponseRepository,
                    'rendezvous'=>$rendezvousRepository,
                    'temoinage'=>$temoinageRepository,
                    'utilisateur'=>$utilisateurRepository];
        $object = $tipos[$tipo]->find($num);

        $object->setActif(!$object->isActif());
        $entityManager->flush();
        $data = [
            'id' => $object->getId(),
            'actif'=>$object->isActif(),
        ];

        return new JsonResponse($data);
    }

    #[Route('/questions', name: 'app_questions', methods: ['GET'])]
    public function questions(QuestionRepository $questionRepository): Response
    {
        return $this->render('admin/questions.html.twig', [
            'questions' => $questionRepository->findBy(['actif'=>true],['date'=>'ASC'],20),
        ]);
    }

    #[Route('/rendezvouses', name: 'app_rendezvouses', methods: ['GET'])]
    public function rendezvouses(RendezvousRepository $rendezvousRepository, EntityManagerInterface $entityManager): Response
    {
        $rendezvousRepository->disablePastRendezvouses();
        return $this->render('admin/rendezvouses.html.twig', [
            'rendezvouses' => $rendezvousRepository->findBy(['actif'=>true],['date'=>'ASC'],20),
        ]);
    }

    #[Route('/reponses', name: 'app_reponses', methods: ['GET'])]
    public function reponses(ReponseRepository $reponseRepository): Response
    {
        return $this->render('admin/reponses.html.twig', [
            'reponses' => $reponseRepository->findBy(['actif'=>true],[],20),
        ]);
    }

    #[Route('/temoinages', name: 'app_temoinages', methods: ['GET'])]
    public function temoinages(TemoinageRepository $temoinageRepository): Response
    {
        return $this->render('admin/temoinages.html.twig', [
            'temoinages' => $temoinageRepository->findBy(['actif'=>true],[],20),
        ]);
    }

    #[Route('/liste/{ref}', name: 'app_utilisateur', methods: ['GET'])]
    public function utilisateurs(string $ref,UtilisateurRepository $utilisateurRepository): Response
    {   
        return $this->render('admin/utilisateur.html.twig', [
            'utilisateurs' => $utilisateurRepository->findByRoleActifAsc('ROLE_'.strtoupper(substr($ref, 0, -1))),
            'ref'=>$ref,
        ]);
    }

    #[Route('/nouveau_membre', name: 'app_nouveau_membre', methods: ['GET','POST'])]
    public function nouveau_membre(Request $request,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() && $form->isValid()) {
            $roles=['admin'=>["ROLE_ADMIN"],'dentiste'=>["ROLE_DENTISTE"],'deux'=>["ROLE_ADMIN","ROLE_DENTISTE"]];
            $utilisateur->setRoles($roles[$request->request->get('roles')]);
            $utilisateur->setActif(true);  
            $utilisateur->setPassword(
                $userPasswordHasher->hashPassword(
                    $utilisateur,
                    $form->get('cin')->getData()
                )
            );
            $utilisateur->setDde(new \DateTime());  
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_nouveau_membre', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/nouveau_membre.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
}

