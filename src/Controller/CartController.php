<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\service\CartService;
use Doctrine\ORM\EntityManager;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{   

    #[Route("/cart/commande", name:'app_commande')]
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cs, EntityManagerInterface $manager, Request $globals): Response
    {
        $cartWithData = $cs->getCartWithData();
        
        $routeName = $globals->get('_route');
        if($routeName == 'app_commande')
        {
            foreach($cartWithData as $product):
                $commande = new Commande;
                $prix = $product['quantite'] * $product['produit']->getPrix();
                $commande->setDateEnregistrement(new \DateTime);
                $commande->setQuantite($product['quantite']);
                $commande->setMontant($prix);
                $commande->setEtat('en cours de traitement');
                $commande->setProduit($product['produit']);
                $commande->setMembre($this->getUser());
                $stock= $product['produit']->getStock();
                $stock -= $product['quantite'];
                $product['produit']->setStock($stock);
                $manager->persist($commande);
                $manager->flush();
                

                $cs->remove($product['produit']->getId());
                
            endforeach;
            $this->addFlash('success', "commande a bien été enregistré !");
            //permet de creer un message qui sera affiché une fois a l'utilisateur
            return $this->redirectToRoute('app_ecommerce');
        }
        
        $total = $cs->getTotal();
        return $this->renderForm('cart/index.html.twig', [
            'items' =>$cartWithData,
            'total' =>$total
        ]);
    }
    #[Route('/cart/add/{id}', name:'cart_add')]
    public function add($id, CartService $cs, Request $request, ProduitRepository $repo) // la classe request stack permet de récupérer la session 
    {
        $quantite = $request->request->get("addQuantite");
        $cs->add($id, $quantite);
        $produit = $repo->find($id);
        $titre = $produit->getTitre();
        $this->addFlash('success', "ajout de $quantite $titre dans votre panier");
         return $this->redirectToRoute('app_ecommerce');
    }
    #[Route("/cart/remove/{id}", name:"cart_remove")]
    public function remove($id, CartService $cs)
    {
        $cs->remove($id);
        return $this->redirectToRoute('app_cart');
    }


    
}
