<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/panier")
 */
class PanierController extends AbstractController
{
    /**
     * Affiche la liste des produits
     * @Route("/", name="panier_index", methods={"GET"})
     */
    public function index(PanierRepository $panierRepository): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }




    /**
     * Permet de valider tous les paniers
     * @Route("/valider", name="validation", methods={"GET","POST"})
     */
    public function validation(PanierRepository $repo, TranslatorInterface $translator): Response
    {
       $panier =  $repo->findAll(); 

       foreach($panier as $item)
       {

            $item->setEtat(1); 
       }
        
       $this->getDoctrine()->getManager()->flush();

       $message = $translator->trans('Panier validé');
       $this->addFlash("success" , $message ); 

        return $this->redirectToRoute('panier_index');
     

     
    }

    /**
     * Permet de supprimer un panier
     * @Route("/{id}", name="panier_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Panier $panier , TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            
            //mise à jour quantité du produit
            $quantite = $panier->getQuantite() +  $panier->getProduit()->getQuantite(); 
            $panier->getProduit()->setQuantite($quantite);
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($panier);
            $entityManager->flush();

            $message = $translator->trans('Produit retiré du panier');
            $this->addFlash("danger" , $message ); 
            

        }

        return $this->redirectToRoute('panier_index');
    }
}
