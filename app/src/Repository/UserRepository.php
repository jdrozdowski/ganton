<?php
/**
 * User repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class UserRepository
 *
 * @package Repository
 */
class UserRepository
{
    /**
     * Doctrine DBAL Connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * UserRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Loads user by login.
     *
     * @param string $login User login
     * @throws UsernameNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function loadUserByLogin($login)
    {
        try {
            $user = $this->getUserByLogin($login);

            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            $roles = $this->getUserRoles($user['user_id']);

            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            return [
                'id' => $user['user_id'],
                'login' => $user['login'],
                'password' => $user['password'],
                'salt' => null,
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Gets user data by login.
     *
     * @param string $login User login
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserByLogin($login)
    {
        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('u.user_id', 'u.login', 'u.password')
                ->from('users', 'u')
                ->where('u.login = :login')
                ->setParameter(':login', $login, \PDO::PARAM_STR);

            return $queryBuilder->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * Gets user roles by User ID.
     *
     * @param integer $userId User ID
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserRoles($userId)
    {
        $roles = [];

        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('r.name')
                ->from('users', 'u')
                ->innerJoin('u', 'roles', 'r', 'u.role_id = r.id')
                ->where('u.user_id = :id')
                ->setParameter(':id', $userId, \PDO::PARAM_INT);
            $result = $queryBuilder->execute()->fetchAll();

            if ($result) {
                $roles = array_column($result, 'name');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
    }

    /**
     * Fetch editable data by id.
     *
     * @param int $id Element Id
     *
     * @return array|mixed
     */
    public function findEditableDataById($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('ud.firstname', 'ud.surname', 'ud.location', 'ud.birthdate', 'ud.height', 'ud.weight')
            ->from('users_data', 'ud')
            ->where('ud.user_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Fetch all records.
     *
     * @param null $string
     *
     * @return array Result
     */
    public function findAllByAdmin($string = null)
    {
        $queryBuilder = $this->queryAll();

        if ($string !== null) {
            $queryBuilder
                ->where('ud.firstname = :name')
                ->orWhere('ud.surname = :surname')
                ->orWhere('ud.location = :location')
                ->setParameter(':name', $string, \PDO::PARAM_STR)
                ->setParameter(':surname', $string, \PDO::PARAM_STR)
                ->setParameter(':location', $string, \PDO::PARAM_STR);
        }

        $result = $queryBuilder->execute()->fetchAll();

        return $result ? $result : [];
    }

    /**
     * Fetch all records by role.
     *
     * @param string $role   User role
     * @param null   $string
     *
     * @return array Result
     */
    public function findAll($role, $string = null)
    {
        $queryBuilder = $this->queryAll();

        if ($string !== null) {
            $queryBuilder
                ->where('ud.firstname = :name')
                ->orWhere('ud.surname = :surname')
                ->orWhere('ud.location = :location')
                ->setParameter(':name', $string, \PDO::PARAM_STR)
                ->setParameter(':surname', $string, \PDO::PARAM_STR)
                ->setParameter(':location', $string, \PDO::PARAM_STR);
        }

        if ($role === 'coach') {
            $queryBuilder
                ->andWhere('u.role_id = 2');
        } elseif ($role === 'athlete') {
            $queryBuilder
                ->andWhere('u.role_id = 3')
                ->setParameter(':role', $role, \PDO::PARAM_STR);
        } else {
            $queryBuilder
                ->andWhere('u.role_id != 1');
        }

        $result = $queryBuilder->execute()->fetchAll();

        return $result ? $result : [];
    }

    /**
     * Find for uniqueness.
     *
     * @param string $name Element name
     *
     * @return array Result
     */
    public function findForUniqueness($name)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.login')
            ->from('users', 'u')
            ->where('u.login = :name')
            ->setParameter(':name', $name, \PDO::PARAM_STR);

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Fetch one record.
     *
     * @param int $userId Element Id
     *
     * @return mixed Result
     */
    public function findOneById($userId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder
            ->select('u.user_id', 'r.name', 'u.login', 'ud.firstname', 'ud.surname', 'ud.location', 'ud.birthdate', 'ud.height', 'ud.weight')
            ->from('users', 'u')
            ->join('u', 'users_data', 'ud', 'u.user_id = ud.user_id')
            ->join('u', 'roles', 'r', 'u.role_id = r.id')
            ->where('u.user_id = :user_id')
            ->setParameter(':user_id', $userId);

        $result = $queryBuilder->execute()->fetch();

        return $result;
    }

    /**
     * Fetch name, surname, username by id.
     *
     * @param int $id Element Id
     *
     * @return array|mixed Result
     */
    public function findNameAndSurnameAndUsernameById($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.login')
            ->from('users', 'u')
            ->where('u.user_id = :user_id')
            ->setParameter(':user_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        if ($result) {
            $result = $result + $this->findUserNameAndSurname($id);
        }

        return $result ? $result : [];
    }


    /**
     * Fetch user id by username.
     *
     * @param string $username Username
     *
     * @return array|mixed Result
     */
    public function findUserIdByUsername($username)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.user_id')
            ->from('users', 'u')
            ->where('u.login = :username')
            ->setParameter(':username', $username, \PDO::PARAM_STR);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }


    /**
     * Save record.
     *
     * @param array      $user     User
     * @param null|array $userData User data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($user, $userData = null)
    {
        $this->db->beginTransaction();

        try {
            if (isset($user['user_id']) && ctype_digit((string) $user['user_id'])) {
                $userId = $user['user_id'];
                unset($user['user_id']);
                $this->db->update('users_data', $user, ['user_id' => $userId]);
            } else {
                $this->db->insert('users', $user);
                $userData['user_id'] = $this->db->lastInsertId();
                $this->db->insert('users_data', $userData);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Save password field.
     *
     * @param string $password Password
     * @param int    $id       Element Id
     *
     * @return int Result
     */
    public function savePassword($password, $id)
    {
        return $this->db->update('users', ['password' => $password], ['user_id' => $id]);
    }

    /**
     * Update record.
     *
     * @param array $user User
     *
     * @return int Result
     */
    public function update($user)
    {
        $userId = $user['user_id'];
        unset($user['user_id']);

        return $this->db->update('users', $user, ['user_id' => $userId]);
    }

    /**
     * Fetch user name and surname by id.
     *
     * @param int $id Element Id
     *
     * @return array|mixed Result
     */
    protected function findUserNameAndSurname($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('ud.firstname', 'ud.surname')
            ->from('users_data', 'ud')
            ->where('ud.user_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('u.user_id', 'r.name', 'u.login', 'ud.firstname', 'ud.surname', 'ud.location', 'ud.birthdate')
            ->from('users', 'u')
            ->join('u', 'users_data', 'ud', 'u.user_id = ud.user_id')
            ->join('u', 'roles', 'r', 'u.role_id = r.id');
    }
}
