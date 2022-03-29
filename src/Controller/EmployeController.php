<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use App\Form\IndentificationType;
use App\Form\InscriptionType;
use App\Entity\Employe;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Form\FormationType;
use Symfony\Component\HttpFoundation\Session\Session;

class EmployeController extends AbstractController
{
    /**
     * @Route("/afficheLesFormationdeplus", name="app_for_plus")
     */
    public function afficheLesFormationDePlus()
    {
        $formation = $this->getDoctrine()->getRepository(formation::class)->findAll();
        if (!$formation ){
            $message = "Pas de formation";
        }
        else{
            $message = null;
        }
        
        return $this->render('employe/formationsup.html.twig',array('ensFormation'=>$formation, 'message'=>$message));
    }

    /**
     * @Route("/modifieInscriptionv2/{id}", name="app_mod_inscription2")
     */
    public function modifieInscriptionv2($id){
        $inscription = $this->getDoctrine()->getManager()->getRepository(Inscription::class)->find($id);
        $inscription->setStatut("R");
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($inscription);
        $manager->flush();
        return $this->redirectToRoute('app_inscrit');
    }

    /**
     * @Route("/modifieInscription/{id}", name="app_mod_inscription")
     */
    public function modifieInscription($id){
        $inscription = $this->getDoctrine()->getManager()->getRepository(Inscription::class)->find($id);
        $inscription->setStatut("A");
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($inscription);
        $manager->flush();
        return $this->redirectToRoute('app_inscrit');
    }

    /**
     * @Route("/afficheLesInscript", name="app_inscrit")
     */
    public function afficheLesInscript()
    {
        $inscription = $this->getDoctrine()->getRepository(inscription::class)->findAll();
        if (!$inscription ){
            $message = "Pas d'employe";
        }
        else{
            $message = null;
        }
        
        return $this->render('employe/listeInscription.html.twig',array('ensInscript'=>$inscription, 'message'=>$message));
    }
    

    /**
     * @Route("/ajoutInscription/{id}", name="app_inscription")
     */
    public function ajoutInscription($id){
        $formation = $this->getDoctrine()->getManager()->getRepository(Formation::class)->find($id);
        $employeId = $this->get('session')->get('employeId');
        $employe = $this->getDoctrine()->getRepository(Employe::class)->find($employeId);
        $inscription = new Inscription();
        $inscription->setFormation($formation);
        $inscription->setEmploye($employe);
        $inscription->setStatut("E");
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($inscription);
        $manager->flush();
        return $this->redirectToRoute('app_for');
    }


    /**
     * @Route("/ajoutEmploye", name="ajoutEmploye")
     */
    public function ajoutEmployeAction(Request $request, $employe= null){
        if($employe == null){
            $employe = new Employe();
        }
        $form = $this->createForm(InscriptionType::class, $employe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            //return $this->render('employe/formation.html.twig', array('form'=>$form->createView()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($employe);
            $em->flush();
            return $this->redirectToRoute('ajoutEmploye');
        }
        //return $this->redirectToRoute('employe');
        return $this->render('employe/formation.html.twig', array('form'=>$form->createView()));

    }

    /**
     * @Route("/ajoutFormation", name="ajoutFormation")
     */
    public function ajoutFormationAction(Request $request, $formation= null){
        if($formation == null){
            $formation = new Formation();
        }
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            //return $this->render('employe/formation.html.twig', array('form'=>$form->createView()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('ajoutFormation');
        }
        //return $this->redirectToRoute('employe');
        return $this->render('employe/formation.html.twig', array('form'=>$form->createView()));

    }


    /**
     * @Route("/identification", name="identification")
     */
    public function identification(Request $request, $emp= null){

        $form = $this->createForm(IndentificationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $login = $form->get('login')->getViewData();
            $mdp = $form->get('mdp')->getViewData();
            $user = $this->getDoctrine()->getRepository(Employe::class)->findBy(
                [
                    'login' => $login,
                    'mdp' => $mdp
                ]
            );
            if($user == null){
                return $this->redirectToRoute('identification');
            }
            else{
                $session = new Session();
                $session->set('employeId', $user[0]->getId());
                if($user[0]->getStatut()==0){
                    return $this->redirectToRoute('app_for_supp');
                }
                elseif($user[0]->getStatut()==1){
                    return $this->redirectToRoute('app_for');
                }
                
            }
        }
        return $this->render('employe/editer.html.twig', array('form'=>$form->createView()));

    }

    /**
     * @Route("/afficheLesEmploye", name="app_emp")
     */
    public function afficheLesEmploye()
    {
        $employe = $this->getDoctrine()->getRepository(employe::class)->findAll();
        if (!$employe ){
            $message = "Pas d'employe";
        }
        else{
            $message = null;
        }
        
        return $this->render('employe/listeemploye.html.twig',array('ensEmploye'=>$employe, 'message'=>$message));
    }

    /**
     * @Route("/afficheLesFormationEmployer/{id}", name="app_emp_form")
     */
    public function afficheLesFormationEmployer($id)
    {
        $inscription = $this->getDoctrine()->getRepository(Inscription::class)->findBy(
            [
                'employe' => $id
            ]
        );

        if (!$inscription){
            $message = "Inscrit dans aucune formation";
        }
        else{
            $message = null;
        }

        return $this->render('employe/listeformationemp.html.twig',array('ensInscription'=>$inscription, 'message'=>$message));
    }

    /**
     * @Route("/afficheLesFormation", name="app_for")
     */
    public function afficheLesFormation()
    {
        $formation = $this->getDoctrine()->getRepository(formation::class)->findAll();
        $inscription = $this->getDoctrine()->getRepository(inscription::class)->findAll();
        if (!$formation ){
            $message = "Pas de formation";
        }
        else{
            $message = null;
        }
        
        return $this->render('employe/listeformation.html.twig',array('ensFormation'=>$formation, 'message'=>$message, 'ensInscription'=>$inscription));
    }

    /**
     * @Route("/afficheLesFormationasupp", name="app_for_supp")
     */
    public function afficheLesFormationsupp()
    {
        $formation = $this->getDoctrine()->getRepository(formation::class)->findAll();
        if (!$formation ){
            $message = "Pas de formation";
        }
        else{
            $message = null;
        }
        
        return $this->render('employe/listeformationasupp.html.twig',array('ensFormation'=>$formation, 'message'=>$message));
    }

    /**
     * @Route("/suppFormation/{id}", name="app_sup")
     */
    public function suppFormation($id)
    {
        $formation = $this->getDoctrine()->getManager()->getRepository(Formation::class)->find($id);
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($formation);
        $manager->flush();
        return $this->redirectToRoute('app_for_supp');
    }

    /**
     * @Route("/formFormation", name="formation")
     */
    public function formation(){

        $form = $this->createForm(FormationType::class);

        return $this->render('employe/listeformation.html.twig', array('form'=>$form->createView()));

    }

    /**
     * @Route("/ajout", name="app_ajout")
     */
    public function ajoutEmploye()
    {
        $employe = new Employe();
        $employe->setLogin("tata");
        $employe->setMdp("tata");
        $employe->setNom("tata");
        $employe->setPrenom("tata");
        $employe->setStatut(1);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($employe);
        $manager->flush();
        return $this->render('Employe/index.html.twig', [
            'controller_name' => 'FilmController',
        ]);

    }
   
    /**
     * @Route("/ajoutEmployer", name="app_employe_ajouter")
     */
    public function ajoutEmployer(Request $request, $employe= null)
    {
        if($employe == null){
            $employe = new Employe();
        }
        $form = $this->createForm(FilmType::class, $employe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($employe);
            $em->flush();
            return $this->redirectToRoute('app_emp');
        }

        return $this->render('employe/editer.html.twig', array('form'=>$form->createView()));
    }


}
