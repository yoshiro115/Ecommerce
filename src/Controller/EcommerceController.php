<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EcommerceController extends AbstractController
{
    #[Route('/', name: 'app_ecommerce')]
    public function index(ProduitRepository $repo): Response
    {
        $produits = $repo->findAll();
        return $this->render('ecommerce/index.html.twig', [
            'produits'=> $produits,
        ]);
    }
    #[Route('/show/{id}', name: 'app_show')]
    public function show(ProduitRepository $repo, $id, RequestStack $rs)
    {
        $produit = $repo->find($id);
        $session = $rs->getSession();
        $cart = $session->get('cart', []);
        if(!empty($cart[$id]))
        {
            $cartquantite = $cart[$id];
            $stock = $produit->getStock();
            $encore= $stock - $cartquantite;
        }
        else{
            $encore = $produit->getStock();
        }
        return $this->render('ecommerce/show.html.twig',[
            'produit' => $produit,
            "maxQuantite" => $encore,
        ]);
    }
    #[Route('/profil' , name:"app_profil" )]
    public function profil(CommandeRepository $repo)
    {
        $commandes = $repo->findAll();


        return $this->render('ecommerce/profil.html.twig', [
            'commandes' => $commandes
        ]);
    }
}
