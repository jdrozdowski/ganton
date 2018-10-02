<?php
/**
 * Invitation repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class InvitationRepository
 *
 * @package Repository
 */
class InvitationRepository
{
    /**
     * Doctrine DBAL Connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * User repository.
     *
     * @var null|\Repository\UserRepository $userRepository
     */
    protected $userRepository = null;

    /**
     * Workout repository.
     *
     * @var null|\Repository\WorkoutRepository $workoutRepository
     */
    protected $workoutRepository = null;

    /**
     * InvitationRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->userRepository = new UserRepository($db);
        $this->workoutRepository = new WorkoutRepository($db);
    }

    /**
     * Fetch all records by user id.
     *
     * @param int $userId User Id
     *
     * @return array Result
     */
    public function findAll($userId)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->select('i.invitation_id', 'i.from_user_id', 'i.workout_id')
            ->from('invitations', 'i')
            ->where('i.to_user_id = :id')
            ->setParameter(':id', $userId);
        $result = $queryBuilder->execute()->fetchAll();

        if ($result) {
            foreach ($result as &$invitation) {
                $invitation['from_user'] = $this->findLinkedUser($invitation['from_user_id']);

                if ($invitation['workout_id']) {
                    $invitation = $invitation + $this->findDueDate($invitation['workout_id']);
                }
            }
            unset($invitation);
        }

        return $result ? $result : [];
    }

    /**
     * Fetch one record by id.
     *
     * @param int $id     Element Id
     * @param int $userId User Id
     *
     * @return array Result
     */
    public function findOneById($id, $userId)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->select('i.invitation_id', 'i.from_user_id', 'i.to_user_id', 'i.workout_id')
            ->from('invitations', 'i')
            ->where('i.invitation = :id')
            ->where('i.to_user_id = :user_id')
            ->setParameter(':id', $id)
            ->setParameter(':user_id', $userId);
        $result = $queryBuilder->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Save record.
     *
     * @param array $invitation Invitation
     *
     * @return int Result
     */
    public function save($invitation)
    {
        return $this->db->insert('invitations', $invitation);
    }

    /**
     * Remove record.
     *
     * @param int $id Element id
     *
     * @return int Result
     */
    public function delete($id)
    {
        return $this->db->delete('invitations', ['invitation_id' => $id]);
    }

    /**
     * Fetch linked user.
     *
     * @param int $id User Id
     *
     * @return array|mixed Result
     */
    protected function findLinkedUser($id)
    {
        return $this->userRepository->findNameAndSurnameAndUsernameById($id);
    }

    /**
     * Fetch workout due date.
     *
     * @param int $id Element Id
     *
     * @return mixed Result
     */
    protected function findDueDate($id)
    {
        return $this->workoutRepository->findDueDateById($id);
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('i.invitation_id', 'i.to_user_id')
            ->from('invitations', 'i');
    }
}
