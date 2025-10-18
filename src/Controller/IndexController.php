<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Temoinage;
use App\Entity\Rendezvous;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Form\TemoinageType;
use App\Form\RendezvousType;
use App\Form\UtilisateurType;
use App\Form\QuestionType;
use App\Repository\UtilisateurRepository;
use App\Repository\TemoinageRepository;
use App\Repository\QuestionRepository;
use App\Repository\RendezvousRepository;

class IndexController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function index(Request $request,UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager, TemoinageRepository $temoinageRepository): Response
    {    
        /* Genera 100 testimonios
        for($i=0; $i<100; $i++) { 
            $patient = $utilisateurRepository->find(rand(4,6));
            $temoinage = new Temoinage();
            $temoinage->setMessage("Temoinage no. $i du patient 1: Lorem ipsum dolor sit, amet consectetur adipisicing elit. Porro, soluta saepe minus excepturi rem exercitationem ab cumque ullam fuga sapiente harum id eveniet consequatur pariatur officiis, eligendi nisi corporis deserunt!");
            $temoinage->setDate( new \DateTime());
            $temoinage->setPatient($patient);
            $temoinage->setActif(true);
            $entityManager->persist($temoinage);
            $temoinage = null;
            $patient = null;
        }
        $entityManager->flush();*/
        $p = $request->query->get('doc_page');
        $page = (!is_numeric($p) || intval($p) != $p) ? 1 : $p  ;
        $limit = 7;
        $numDent = $utilisateurRepository->countByRoleAndActif();
        $lastPage = $numDent % $limit == 0  ? $numDent/$limit : intval($numDent/ $limit) + 1;
        $page = $page < 1 ? 1 : $page;
        $page = $lastPage < $page ? $lastPage: $page;

        $temoinage = $temoinageRepository->findByRandom(10);
        $pages = array('prev'=> $page==1? 1 : $page-1,      'before2'=>$page>2 ? $page-2: null,
                     'before1'=>$page>1 ? $page-1:null,    'actual'=>$page, 
                     'after1'=>$lastPage-$page > 0 ? $page+1: null,
                     'after2'=>$lastPage-$page > 1 ? $page+2: null,
                     'next'=>$page!=$lastPage ? $page+1 : $lastPage, 'last'=>$lastPage);
        shuffle($temoinage);
        return $this->render('index/index.html.twig', [
            'dentistes' => $utilisateurRepository->findActifDentistesAsc($page, $limit), 
            'page'=>$pages,
            'temoinages' =>$temoinage,
        ]);
    }

    #[Route('/cargar_dentistas', name: 'app_cargar_dentistas', methods: ['GET'])]
    public function cargar_dentistas(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {   $ville = $request->request->get('ciudad');
        $users = $utilisateurRepository->findBy(array('actif'=>true,'ville'=>$ville), array('nom'=>'ASC')); 
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
            ];
        }
        return new JsonResponse($data);
    }

    #[Route('/after_login', name: 'after_login', methods: ['GET'])]
    public function after_login(Request $request, UtilisateurRepository $utilisateurRepository)
    {  
        $ruta = in_array('ROLE_DENTISTE',$this->getUser()->getRoles()) ?  'app_dentiste' : 'app_profile';
        $ruta = in_array('ROLE_ADMIN',$this->getUser()->getRoles()) ?  'app_dashboard' : $ruta;

       return $this->redirectToRoute($ruta, [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/docteur/{codigo}', name: 'app_docteur', methods: ['GET'])]
    public function docteur($codigo,UtilisateurRepository $utilisateurRepository): Response
    {
        $id = $codigo/((103-1)*(101-1));
        $dentiste= $utilisateurRepository->find($id);
        return $this->render('index/voir_dentiste.html.twig', [
        'dentistes'=> $utilisateurRepository->findByRoleVilleActifAsc('ROLE_DENTISTE',$dentiste->getVilleRef()),
        'utilisateur' => $dentiste,
       
    ]);}

    #[Route('/patient/{codigo}', name: 'app_patient', methods: ['GET'])]
    public function patient($codigo, UtilisateurRepository $utilisateurRepository): Response
    {
        $id = $codigo/((103-1)*(101-1));
          
        return $this->render('index/historique.html.twig', [ 
        'utilisateur' => $utilisateurRepository->find($id),
        'rendezvouses'=>$utilisateurRepository->find($id)->getRendezvouses_patient(),
    ]);}

    #[Route('/verificar_consulta', name: 'app_verificar_consulta')]
    public function consulta(Request $request, UtilisateurRepository $utilisateurRepository, RendezvousRepository $rendezvousRepository): JsonResponse
    { 
        $user = $this->getUser();
        $fecha = $request->request->get('fecha');
        $dentiste = $utilisateurRepository->find($request->request->get('dentista'));
        $date = new \DateTime($fecha);
        $rendezvous = $rendezvousRepository->findBy(['date'=>$date,'actif'=>true,'dentiste'=>$dentiste]);
        $mensaje = $rendezvous != null ? '1': '0';
        $rendezvouses = $rendezvousRepository->findUserRVActif($user, $date->format('Y-m-d')); 
        $mensaje = count($rendezvouses) > 0 ? '2' : $mensaje;
        $data = [
          
        'mensaje' => $mensaje, 
        ];

       return new JsonResponse($data);
    }

   #[Route('/proponer_hora', name: 'app_proponer_hora')]
    public function proponer_hora(Request $request, UtilisateurRepository $utilisateurRepository, RendezvousRepository $rendezvousRepository): JsonResponse
   { 
        $user = $this->getUser();
        $fecha = $request->request->get('fecha');
        $dentiste = $utilisateurRepository->find($request->request->get('dentista'));
        $rendezvous = $rendezvousRepository->findConsultaByHoy($fecha, $dentiste);
        if(count($rendezvous)){
            $h= intval($rendezvous[(count($rendezvous)-1)]->getDate()->format('H'));
            $m= intval($rendezvous[(count($rendezvous)-1)]->getDate()->format('i'));
            $minuto = $m> 45 ? '00': $m+15;
            $hora = $m > 45 ? $h+1: $h;

        }
        else{
            $hora = '0';
            $minuto = '0';
        }
        
        $data = [    
        'hora' => $hora, 
        'minuto' => $minuto, 
        ];

      return new JsonResponse($data);
    }

   #[Route('/verificar_consulta_dentista', name: 'app_verificar_consulta_dentista')]
    public function verificar_consulta_dentista(Request $request, EntityManagerInterface $entityManager, RendezvousRepository $rendezvousRepository): JsonResponse
    {  
        $date = new \DateTime($request->request->get('fecha'));
        $rendezvou=$rendezvousRepository->find($request->request->get('id'));
        $rendezvous = $rendezvousRepository->findBy(['date'=>$date,'actif'=>true,'dentiste'=>$rendezvou->getDentiste()]);
        $mensaje = $rendezvous != null ? '1': '0';
        $rendezvouses = $rendezvousRepository->findUserRVActif($rendezvou->getPatient(), $date->format('Y-m-d')); 
        $mensaje = count($rendezvouses) > 1 ? '2' : $mensaje;

        if($mensaje == '0'){
            $rendezvou->setDate($date);
            $entityManager->persist($rendezvou);
            $entityManager->flush();
        }  
        $data = [
        'mensaje' => $mensaje, 
        ];

       return new JsonResponse($data);
    }

   #[Route('/verificar', name: 'app_verificar')]
    public function verificar(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $email = $request->request->get('email');
        $cin = $request->request->get('cin');

        $utilisateurEmail = $utilisateurRepository->findOneBy(array('email'=>$email));
        $utilisateurCin = $utilisateurRepository->findOneBy(array('cin'=>$cin));

        $mensaje = $utilisateurCin != null ? '2': '0';
        $mensaje = $utilisateurEmail != null ? '1' :  $mensaje;
        $response = [
            'mensaje' => $mensaje,
        ];

        return new JsonResponse($response);
    }
    
    #[Route('/nouveau_rendezvous', name: 'app_nouveau_rendezvous', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository): Response
    {
        $rendezvou = new rendezvous();
        $form = $this->createForm(RendezvousType::class, $rendezvou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $min = $request->request->get('minute');
            $heure = $request->request->get('heure');
            $dentiste = $utilisateurRepository->find($request->request->get('dentiste'));
            $rendezvou->getDate()->setTime($heure, $min,0,0);
            $rendezvou->setPatient($this->getUser());
            $rendezvou->setDentiste($dentiste);
            $rendezvou->setActif(true);
            $rendezvou->setObservation("");
            $entityManager->persist($rendezvou);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('index/nouveau_rendez_vous.html.twig', [
            'rendezvou' => $rendezvou,
            'form' => $form,
        ]);
    }

    #[Route('/prochains_rendez_vous', name: 'prochains_rendez_vous')]
    public function prochains_rendez_vous(Request $request, EntityManagerInterface $entityManager, RendezvousRepository $rendezvousRepository): Response
    {   $user = $this->getUser();       
        return $this->render('index/calendrier.html.twig', [
            'rendezvouses' => $rendezvousRepository->findBy(['actif'=>true, 'dentiste'=>$user],['date'=>'ASC']),
        ]);
    }

    #[Route('/complete/{id}', name: 'app_rendezvous_complete', methods: ['GET'])]
    public function activer(Request $request, Rendezvous $rendezvous, EntityManagerInterface $entityManager): Response
    {
        $rendezvous->$request->request->get('rendezvous_id');
        $rendezvous->setActif(!$rendezvous->isActif());
        $rendezvous->setObservation($request->request->get('ordenance'));
        $referer = $request->headers->get('referer');
        $entityManager->flush();
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirectToRoute('app_calendrier');
    }
    
    #[Route('/nouveau_temoinage', name: 'nouveau_temoinage', methods: ['GET', 'POST'])]
    public function nouveau_temoinage(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $temoinage = new Temoinage();
        $form = $this->createForm(TemoinageType::class, $temoinage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $temoinage->setDate(new \DateTime());
            $temoinage->setActif(true);
            $temoinage->setPatient($this->getUser());
            $entityManager->persist($temoinage);
            $entityManager->flush();
            $currentRoute = $request->attributes->get('_route');

            return $this->redirectToRoute($currentRoute, [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('index/nouveau_temoinage.html.twig',[
            'form' => $form,
            'temoinages' => $user->getTemoinages(),
        ]);
        
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        //$buscar = glob($this->getParameter('userimages').'/photouser'.$user->getId().".jpg");
        //$fuente = count($buscar)==1 ? 'photouser'.$user->getId().".jpg" : 'user.jpg';
        
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        $formuser = $this->createForm(UtilisateurType::class, $user);
        $formuser->handleRequest($request);
    
        if ($formuser->isSubmitted() && $formuser->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $request->files->get('photo');
            
            if ($photoFile) {

                if ($photoFile->getSize() > 3*1024*1024 || !in_array($photoFile->getMimeType(), ['image/jpeg','image/png','image/jpg'])) {
                    $request->getSession()->set('errorMessage','Verifica que la imagen cumpla con los requisitos indicados.');
                    return $this->redirectToRoute('app_error', [], Response::HTTP_SEE_OTHER);
                }
        
                try {
                    $photoFile->move(
                       'assets/img/userimages/',
                        'photouser'.$user->getId().'.jpg'
                    );
                } catch (FileException $e) {
                    $request->getSession()->set('errorMessage','Ocurrió un error al subir la imagen.');
                    return $this->redirectToRoute('app_error', [], Response::HTTP_SEE_OTHER);
                }
            }
        
            $entityManager->flush();
            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }
        
        if ($form->isSubmitted() && $form->isValid()) {
            $question->setDate(new \DateTime());  
            $question->setActif(true);
            $question->setPatient($this->getUser());            
            //$utilisateurRepository = $entityManager->getRepository(Utilisateur::class);           
            $entityManager->persist($question);
            $entityManager->flush();
            $currentRoute = $request->attributes->get('_route');

            return $this->redirectToRoute($currentRoute, [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('index/profile.html.twig', [
            'utilisateur' => $user,
            'rendezvouses' => $user->getRendezvouses_patient(),
            'question' => $question,
            'form' => $form,
            'formuser' => $formuser,
        ]);
    }

    #[Route('/dentiste', name: 'app_dentiste', methods: ['GET', 'POST'])]
    public function dentiste(Request $request, EntityManagerInterface $entityManager, QuestionRepository $questionRepository): Response
    {
        $user = $this->getUser();
        
        $formuser = $this->createForm(UtilisateurType::class, $user);
        $formuser->handleRequest($request);
        if ($formuser->isSubmitted() && $formuser->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $request->files->get('photo');
            
            if ($photoFile) {

                if ($photoFile->getSize() > 3*1024*1024 || !in_array($photoFile->getMimeType(), ['image/jpeg','image/png','image/jpg'])) {
                    $request->getSession()->set('errorMessage','Verifica que la imagen cumpla con los requisitos indicados.');
                    return $this->redirectToRoute('app_error', [], Response::HTTP_SEE_OTHER);
                }
        
                try {
                    $photoFile->move(
                       'assets/img/userimages/',
                        'photouser'.$user->getId().'.jpg'
                    );
                } catch (FileException $e) {
                    $request->getSession()->set('errorMessage','Ocurrió un error al subir la imagen.');
                    return $this->redirectToRoute('app_error', [], Response::HTTP_SEE_OTHER);
                }
            }
        
            $entityManager->flush();
            return $this->redirectToRoute('app_dentiste', [], Response::HTTP_SEE_OTHER);
        }

        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository = $entityManager->getRepository(Question::class); 
            //dd($request);
            $reponse->setQuestion($questionRepository->find(intval($request->request->get('question_id'))));
            $reponse->setDate(new \DateTime());
            $reponse->setDentiste($this->getUser());
            $reponse->setActif(true);
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_dentiste', [], Response::HTTP_SEE_OTHER);
        }
        $idQR=array();
        $ids=array();
        $questionNR=array();
        $questiones=array();
        $reponses = $user->getReponses();
        foreach($reponses as $repons ){
            array_push($idQR, $repons->getQuestion()->getId());
            $questiones[$repons->getQuestion()->getId()] = $repons->getQuestion();
        }
        $questions = $questionRepository->findBy(['actif'=> true],[],10,0);
        //dd($questions);
        foreach($questions as $question ){
            array_push($ids, $question->getId());
            $questiones[$question->getId()] = $question;
        }
        $idQNR= array_diff($ids, $idQR);
        foreach($idQNR as $id){
            array_push($questionNR,$questiones[$id]); 
        }

        return $this->render('index/profile_dentiste.html.twig', [
            'utilisateur' => $user,
            'rendezvouses' => $user->getRendezvouses_dentiste(),
            'questions' => $questionNR,
            'reponse' => $reponse,
            'form' => $form,
            'formuser' => $formuser,
        ]);
    }

    #[Route('/aporpos', name: 'app_apropos')]
    public function apropos(): Response
    {return $this->render('index/apropos.html.twig', []);}

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {return $this->render('index/services.html.twig', []);}

    #[Route('/service/{ref}', name: 'app_service')]
    public function service(string $ref): Response
    {   $services = array(
        'blanchissement_dentaire'=>array('Blanchissement dentaire','En Denticare, nous croyons qu\'un sourire éclatant peut faire une grande différence dans votre vie personnelle et professionnelle. Si vous recherchez un moyen sûr et efficace d’améliorer l’apparence de vos dents, notre service de blanchiment des dents est la solution idéale. Nous utilisons des techniques avancées et des produits de haute qualité pour garantir des résultats visibles et durables. Découvrez comment nous pouvons vous aider à obtenir le sourire éblouissant dont vous avez toujours rêvé.'),
        'extraction_dentaire'=> array('Extraction dentaire','En Denticare, nous comprenons que l\'idée d\'une extraction dentaire peut générer de l\'anxiété. Cependant, notre approche professionnelle et compatissante est conçue pour que vous vous sentiez à l\'aise et en confiance tout au long du processus. Nous utilisons des techniques avancées et des soins personnalisés pour garantir que chaque extraction dentaire soit aussi sûre, efficace et indolore que possible.'),
        'implants_dentaires'=> array('Implantes Dentaires','En Denticare, nous comprenons que la perte de dents peut affecter non seulement votre santé bucco-dentaire, mais aussi votre confiance et votre qualité de vie. C\'est pourquoi nous nous spécialisons dans l\'offre de solutions d\'implants dentaires de la plus haute qualité. Nos implants dentaires restaurent non seulement votre sourire, mais offrent également une fonctionnalité complète et une apparence naturelle. Découvrez comment nous pouvons vous aider à retrouver votre sourire et à améliorer votre vie.'),
        'orthodontie'=>array('Orthodontie','En Denticare, nous comprenons qu\'un sourire aligné et beau améliore non seulement votre apparence, mais aussi votre santé dentaire. Notre service d\'orthodontie est conçu pour vous offrir des solutions avancées et personnalisées, vous assurant d\'obtenir des résultats optimaux et une expérience confortable à chaque étape du traitement.'),
        'ponts_dentaires'=>array('Ponts Dentaires','En Denticare, nous savons que la perte d\'une ou plusieurs dents peut affecter considérablement votre santé bucco-dentaire et votre estime de soi. C\'est pourquoi nous proposons des solutions de ponts dentaires de haute qualité pour vous aider à retrouver votre sourire et la fonctionnalité de votre bouche. Nos ponts dentaires sont conçus pour s’intégrer parfaitement à vos dents naturelles, offrant ainsi une solution durable et esthétique.'),
        'revision'=>array('Révision','En Denticare, nous croyons fermement à l’importance de la prévention pour maintenir une santé bucco-dentaire optimale. Nos examens dentaires réguliers sont conçus pour identifier et traiter les problèmes avant qu’ils ne deviennent graves, garantissant ainsi que votre sourire reste sain et éclatant. Avec une approche globale et personnalisée, nous nous engageons à vous prodiguer les meilleurs soins lors de chaque visite.'),
    );
        
        return $this->render('index/service.html.twig', [
        'ref'=>$ref,
        'phrase'=>$services[$ref][1],
        'service'=>$services[$ref][0],
    ]);}

    #[Route('/clinique/{ref}', name: 'app_clinique')]
    public function clinique(string $ref, UtilisateurRepository $utilisateurRepository): Response
    {
        $cliniques = array(
            'al_hoceima' => 'Al Hoceïma',
            'casablanca' => 'Casablanca',
            'fes' => 'Fès',
            'kenitra' => 'Kénitra',
            'marrakech' => 'Marrakech',
            'meknes' => 'Meknès',
            'rabat' => 'Rabat',
            'sale' => 'Salé',
            'tanger' => 'Tanger',
            'tetouan' => 'Tétouan'
        );


    
        // Pasa los datos al template
        return $this->render('index/clinique.html.twig', [
            'dentistes' => $utilisateurRepository->findByRoleVilleActifAsc('ROLE_DENTISTE',$ref),
            'ref' => $ref,
            'clinique' => $cliniques[$ref],
        ]);
    }
    

    #[Route('/cliniques', name: 'app_cliniques')]
    public function cliniques(): Response
    {
        $cliniques = [
            ['ref' => 'al_hoceima', 'name' => 'Al Hoceïma', 'image' => 'al_hoceima.jpg'],
            ['ref' => 'casablanca', 'name' => 'Casablanca', 'image' => 'casablanca.jpg'],
            ['ref' => 'fes', 'name' => 'Fès', 'image' => 'fes.jpg'],
            ['ref' => 'kenitra', 'name' => 'Kénitra', 'image' => 'kenitra.jpg'],
            ['ref' => 'marrakech', 'name' => 'Marrakech', 'image' => 'marrakech.jpg'],
            ['ref' => 'meknes', 'name' => 'Meknès', 'image' => 'meknes.jpg'],
            ['ref' => 'rabat', 'name' => 'Rabat', 'image' => 'rabat.jpg'],
            ['ref' => 'sale', 'name' => 'Salé', 'image' => 'sale.jpg'],
            ['ref' => 'tanger', 'name' => 'Tanger', 'image' => 'tanger.jpg'],
            ['ref' => 'tetouan', 'name' => 'Tétouan', 'image' => 'tetouan.jpg'],
        ];
    
        return $this->render('index/cliniques.html.twig', [
            'cliniques' => $cliniques,
        ]);
    }
    
   /* #[Route('/error', name: 'app_error')]
    public function error(Request $request): Response
    {   $message= $request->getSession()->get('errorMessage');
        $request->getSession()->remove('errorMessage');
        return $this->render('index/error.html.twig', [ 'mensaje'=>$message,]);
    }*/


}

