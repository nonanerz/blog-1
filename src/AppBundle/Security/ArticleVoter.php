<?php


namespace AppBundle\Security;


use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    const CREATE_ARTICLE = 'create';
    const EDIT_ARTICLE = 'edit';
    const DELETE_ARTICLE = 'delete';

    private $decisionManager;
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CREATE_ARTICLE, self::EDIT_ARTICLE, self::DELETE_ARTICLE))) {
            return false;
        }
        if (!$subject instanceof Article) {
            return false;
        }
        return true;
    }
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var Article $article */
        $article = $subject;
        if (!$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::CREATE_ARTICLE:
                if ($this->decisionManager->decide($token, ['ROLE_ADMIN', 'ROLE_USER'])) {
                    return true;
                }
                break;
            case (self::EDIT_ARTICLE || self::DELETE_ARTICLE):
                if (($this->decisionManager->decide($token, ['ROLE_ADMIN'])) ||
                    $user->getId() === $article->getAuthor()->getUser()->getId()
                ) {
                    return true;
                }
        }
        return false;
    }

}