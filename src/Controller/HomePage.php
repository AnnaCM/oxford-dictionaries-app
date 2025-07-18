<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePage extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     */
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }
}
