<?php
/**
 * Webservice User.
 */
namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * Class WebserviceUser
 *
 * @package AppBundle\Security\User
 */
class WebserviceUser implements UserInterface, EquatableInterface
{
    /**
     * User Id.
     *
     * @var int Id
     */
    private $id;

    /**
     * User name.
     *
     * @var string User name
     */
    private $username;

    /**
     * Password.
     *
     * @var string password
     */
    private $password;

    /**
     * Salt.
     *
     * @var string salt
     */
    private $salt;

    /**
     * User roles.
     *
     * @var array roles
     */
    private $roles;

    /**
     * WebserviceUser constructor.
     *
     * @param int    $id       User  Id
     * @param string $username User name
     * @param string $password User password
     * @param string $salt     Salt
     * @param array  $roles    User roles
     */
    public function __construct($id, $username, $password, $salt, array $roles)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    /**
     * Return user id.
     *
     * @return int User Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Compare user data.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
