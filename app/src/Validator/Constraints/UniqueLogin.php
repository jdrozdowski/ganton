<?php
/**
 * Unique Login constraint.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueLogin
 *
 * @package Validator\Constraints
 */
class UniqueLogin extends Constraint
{
    /**
     * Message.
     *
     * @var string $message
     */
    public $message = '{{ login }} is not unique Login';

    /**
     * User repository.
     *
     * @var null|\Repository\UserRepository $repository
     */
    public $repository = null;
}
