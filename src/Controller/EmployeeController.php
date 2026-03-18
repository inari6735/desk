<?php

namespace App\Controller;

use App\Enum\Position;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class EmployeeController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $positionFilter = $request->query->getString('position');
        $position = Position::tryFrom($positionFilter);

        $employees = $userRepository->findEmployees($position);

        return $this->render('employee/index.html.twig', [
            'employees' => $employees,
            'positions' => Position::cases(),
            'currentPosition' => $position,
        ]);
    }
}
