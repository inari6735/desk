<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig');
    }

    #[Route('/toggle-dark-mode', name: 'app_settings_toggle_dark_mode', methods: ['POST'])]
    public function toggleDarkMode(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('toggle-dark-mode', $request->getPayload()->getString('_token'))) {
            $user->setDarkMode(!$user->isDarkMode());
            $em->flush();

            $this->addFlash('success', $user->isDarkMode() ? 'Dark mode enabled.' : 'Dark mode disabled.');
        }

        return $this->redirectToRoute('app_settings');
    }
}
