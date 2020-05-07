<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class ProduitController extends AbstractController
{
    /**
     * Liste des produits
     * @Route("/", name="produit_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    /**
     * Permet d'ajouter un produit
     * @Route("/produit/new", name="produit_new", methods={"GET","POST"})
     */
    public function new(Request $request , TranslatorInterface $translator): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $image = $produit->getPhoto();
            $imageName = md5(uniqid()) . '.' . $image->guessExtension();
            $image->move($this->getParameter('produitImg'), $imageName);
            $produit->setPhoto($imageName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            $message = $translator->trans('Produit ajouté');
            $this->addFlash("success" , $message ); 
         
            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }



    /**
     * Permet de voir la fiche d'un produit
     * @Route("/produit/{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }


    /**
     * Permet de supprimer un produit
     * @Route("/produit/{id}", name="produit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Produit $produit , TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();

            $message = $translator->trans('Produit supprimé');
            $this->addFlash("danger" , $message ); 
        }

        return $this->redirectToRoute('produit_index');
    }


    /**
     * Permet d'ajouter un produit au panier
     * @Route("/produit/add/panier/{id}" , name="add_panier" , methods={"POST"})
     */
    public function addPanier(Request $request , Produit $produit , TranslatorInterface $translator)
    {
        $quantite = $request->request->get('quantite'); 

        $Panier = new Panier(); 
        $Panier->setProduit($produit); 
        $Panier->setQuantite($quantite); 
        $Panier->setEtat(0); 

        $quantite_produit = $produit->getQuantite() - $quantite ; 

        $produit->setQuantite($quantite_produit); 

        $em = $this->getDoctrine()->getManager();
        $em->persist($Panier); 
        $em->flush(); 

        $message = $translator->trans('Produit ajouté au panier');
        $this->addFlash("success" , $message ); 

        return $this->redirectToRoute('produit_index');

    }



}
