<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilType;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/{id<\d+>}',name:'user_detail')]
    public function detail(User $user) {

        if($user==null) {
            $this->addFlash('danger', 'Pas sûr que ça soit un user');
            return $this->redirectToRoute('list_post');
        }

        return $this->render('user/detail.html.twig', [
            "user"=>$user,
        ]);
    }

    #[Route('/user/update/{id<\d+>}',name:'user_update')]
    public function update(User $user = null, EntityManagerInterface $entityManager, Request $request,UserPasswordHasherInterface $userPasswordHasher,SluggerInterface $slugger): Response
    {
        if ($user !== $this->getUser()) {
            $this->addFlash('danger', 'Vous n\'êtes pas la bonne personne !');
            return $this->redirectToRoute('list_post');
        }

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //gestion de la photo
            /** @var UploadedFile $dossierPhotos */
            $dossierPhotos = $form->get('avatar')->getData();

            if ($dossierPhotos){
                $nomOriginalDeFichier = pathinfo($dossierPhotos->getClientOriginalName(), PATHINFO_FILENAME);
                //on change le nom du fichier
                $nomDeFichierSecur = $slugger->slug($nomOriginalDeFichier);
                $nomDeFichier = $nomDeFichierSecur.'-'.uniqid().'.'.$dossierPhotos->guessExtension();
                try{
                    $dossierPhotos->move(
                        $this->getParameter('photo_dossier'),
                        $nomDeFichier
                    );
                }catch (FileException $e){
                    $this->addFlash('error',"Soucis lors de l'enregistrement. Désolé");
                }
                $user->setAvatar($nomDeFichier);

            }

            //gestion des password
            $mdp = $form->get('plainPassword')->getData();
            //si ancien password et user pseudo/email est bon
            if($userPasswordHasher->isPasswordValid($this->getUser(), $mdp)){
                //et si quelque chose est noté dans le champ new_password
                //on change le password en BD
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->flush();
                $this->addFlash('success', 'Les modifications ont bien été prise en compte!');
                return $this->redirectToRoute('user_profil',['id'=>$user->getId()]);
            }else{
                $form->get('plainPassword')->addError(new FormError('mot de passe erroné.'));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié.');
            return $this->redirectToRoute('user_detail');
        }
        return $this->render
        ('user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
