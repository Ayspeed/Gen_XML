<?php
namespace App\Controller;

use App\Entity\Client;
use App\Entity\Command;
use App\Entity\Produit;
use App\Form\ClientType;
use App\Form\CommandType;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{
    #[Route('/client/new', name: 'client_new')]
    public function newClient(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();
            return $this->redirectToRoute('client_new'); // ou une autre route
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/command/new', name: 'command_new')]
    public function newCommand(Request $request, EntityManagerInterface $em): Response
    {
        $command = new Command();
        $form = $this->createForm(CommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($command);
            $em->flush();
            return $this->redirectToRoute('command_new');
        }

        return $this->render('command/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/new', name: 'produit_new')]
    public function newProduit(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            return $this->redirectToRoute('produit_new');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
