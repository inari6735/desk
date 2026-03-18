<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use App\Enum\ProjectPermission;
use App\Repository\ProjectMemberRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    public function __construct(
        private readonly ProjectMemberRepository $memberRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Project && ProjectPermission::tryFrom($attribute) !== null;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Admins can do everything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        /** @var Project $project */
        $project = $subject;

        $membership = $this->memberRepository->findMembership($project, $user);

        if ($membership === null) {
            return false;
        }

        return $membership->hasPermission($attribute);
    }
}
