<?php

namespace AppBundle\Security;

use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentVoter extends Voter
{
    const CREATE_COMMENT = 'create';
    const DELETE_COMMENT = 'delete';

    private $decisionManager;
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE_COMMENT, self::DELETE_COMMENT])) {
            return false;
        }
        if (!$subject instanceof Comment) {
            return false;
        }

        return true;
    }
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var Comment $article */
        $comment = $subject;
        if (!$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::CREATE_COMMENT:
                if ($this->decisionManager->decide($token, ['ROLE_ADMIN', 'ROLE_USER'])) {
                    return true;
                }
                break;
            case self::DELETE_COMMENT:
                if (($this->decisionManager->decide($token, ['ROLE_ADMIN'])) ||
                    $user->getId() === $comment->getAuthor()->getUser()->getId()
                ) {
                    return true;
                }
        }

        return false;
    }
}
