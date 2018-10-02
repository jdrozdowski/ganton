<?php
/**
 * Exercise repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class ExerciseRepository
 *
 * @package Repository
 */
class ExerciseRepository
{
    /**
     * Doctrine DBAL Connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * ExerciseRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Fetch one record by id.
     *
     * @param int $id Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('e.name', 'e.record')
            ->from('exercises', 'e')
            ->where('e.exercise_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Fetch one record by name.
     *
     * @param string $name Exercise name
     *
     * @return array|mixed Result
     */
    public function findOneByName($name)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('e.name = :name')
            ->setParameter(':name', $name, \PDO::PARAM_STR);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Find exercises by Ids.
     *
     * @param array $ids Exercises Ids.
     *
     * @return array
     */
    public function findById($ids)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('e.exercise_id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $queryBuilder->execute()->fetchAll();
    }


    /**
     * Save record.
     *
     * @param array $exercise Exercise
     *
     * @return mixed Result
     */
    public function save($exercise)
    {
        if (isset($exercise['exercise_id']) && ctype_digit((string) $exercise['exercise_id'])) {
            $id = $exercise['exercise_id'];
            unset($exercise['exercise_id']);

            return $this->db->update('exercises', $exercise, ['exercise_id' => $id]);
        } else {
            $this->db->insert('exercises', $exercise);
            $exercise['exercise_id'] = $this->db->lastInsertId();

            return $exercise;
        }
    }

    /**
     * Save exercise record.
     *
     * @param int   $id     Element Id
     * @param float $record Exercise record
     *
     * @return int Result
     */
    public function saveRecord($id, $record)
    {
        return $this->db->update('exercises', ['record' => $record], ['exercise_id' => $id]);
    }

    /**
     * Remove record.
     *
     * @param int $id Element Id
     */
    public function delete($id)
    {
        if (!($this->findLinkedElements($id))) {
            $this->db->delete('exercises', ['exercise_id' => $id]);
        }
    }

    /**
     * Fetch linked element.
     *
     * @param int $id Element Id
     *
     * @return bool|string
     */
    protected function findLinkedElements($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('de.exercise_id')
            ->from('workout_days_has_exercises', 'de')
            ->where('de.exercise_id = :exercise_id')
            ->setParameter(':exercise_id', $id);
        $result = $queryBuilder->execute()->fetchColumn();

        if (!$result) {
            $queryBuilder->select('we.exercise_id')
                ->from('workouts_has_exercises', 'we')
                ->where('we.exercise_id = :exercise_id')
                ->setParameter(':exercise_id', $id);
            $result = $queryBuilder->execute()->fetchColumn();
        }

        return $result;
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('e.exercise_id', 'e.name', 'e.record')
            ->from('exercises', 'e');
    }
}
