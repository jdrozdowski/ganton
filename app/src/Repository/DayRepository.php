<?php
/**
 * Day repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Class DayRepository
 *
 * @package Repository
 */
class DayRepository
{
    /**
     * Doctrine DBAL Connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * Exercise repository.
     *
     * @var null|\Repository\ExerciseRepository  $exerciseRepository
     */
    protected $exerciseRepository = null;

    /**
     * DayRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->exerciseRepository = new ExerciseRepository($db);
    }

    /**
     * Fetch all records by routine id.
     *
     * @param int $routineId Routine id
     *
     * @return array Result
     */
    public function findAllByRoutineId($routineId)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('wd.workout_routine_id = :routine_id')
            ->orderBy('wd.weekday', 'ASC')
            ->setParameter(':routine_id', $routineId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        if ($result) {
            foreach ($result as &$day) {
                $day['weekday'] = $this->convertNumberToDay($day['weekday']);
                $day['exercises'] = $this->findLinkedExercises($day['workout_day_id']);
            }
            unset($day);
        }

        return $result ? $result : [];
    }

    /**
     * Fetch one by day id and routine id.
     *
     * @param int $id        Day id
     * @param int $routineId Routine id
     *
     * @return array|mixed Result
     */
    public function findOneByIdAndRoutineId($id, $routineId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('wd.workout_routine_id', 'wd.workout_day_id', 'wd.weekday')
            ->from('workout_days', 'wd')
            ->where('wd.workout_routine_id = :routine_id')
            ->andWhere('wd.workout_day_id = :day_id')
            ->setParameter(':routine_id', $routineId, \PDO::PARAM_INT)
            ->setParameter(':day_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        if ($result) {
            $result['weekday'] = $this->convertNumberToDay($result['weekday']);
            $result['exercises'] = $this->findLinkedExercises($id);
        }

        return $result ? $result : [];
    }

    /**
     * Fetch days ids by routine id.
     *
     * @param int $routineId Routine id
     *
     * @return array Result
     */
    public function findIdsByRoutineId($routineId)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('wd.workout_day_id')
            ->from('workout_days', 'wd')
            ->where('wd.workout_routine_id = :routine_id')
            ->setParameter(':routine_id', $routineId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? array_column($result, 'workout_day_id') : [];
    }

    /**
     * Save record.
     *
     * @param array $day Day
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($day)
    {
        $this->db->beginTransaction();

        try {
            $dayExercises = isset($day['exercises']) ? $day['exercises'] : [];
            unset($day['exercises']);

            if (isset($day['workout_day_id']) && ctype_digit((string) $day['workout_day_id'])) {
                $dayId = $day['workout_day_id'];
                unset($day['workout_routine_id'], $day['workout_day_id']);
                $this->removeLinkedExercises($dayId);
                if (isset($dayExercises)) {
                    $this->addLinkedExercises($dayId, $dayExercises);
                }
                $this->db->update('workout_days', $day, ['workout_day_id' => $dayId]);
            } else {
                $this->db->insert('workout_days', $day);
                $dayId = $this->db->lastInsertId();
                if (isset($dayExercises)) {
                    $this->addLinkedExercises($dayId, $dayExercises);
                }
            }

            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete action.
     *
     * @param int $id Element Id
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function delete($id)
    {
        $this->db->beginTransaction();

        try {
            $this->removeLinkedExercises($id);
            $this->db->delete('workout_days', ['workout_day_id' => $id]);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Count days.
     *
     * @param int $routineId Routine id
     *
     * @return int Result
     */
    public function countDays($routineId)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('workout_routine_id = :routine_id')
            ->setParameter(':routine_id', $routineId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->rowCount();

        return $result ? $result : 0;
    }

    /**
     * Fetch linked exercises ids.
     *
     * @param int $id Day id
     *
     * @return array Result
     */
    protected function findLinkedExercisesIds($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('de.exercise_id')
            ->from('workout_days_has_exercises', 'de')
            ->where('de.workout_day_id = :day_id')
            ->setParameter(':day_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? array_column($result, 'exercise_id') : [];
    }

    /**
     * Save linked exercises records.
     *
     * @param int $id Day id
     * @param array $dayExercises Day exercises
     */
    protected function addLinkedExercises($id, $dayExercises)
    {
        if ($dayExercises) {
            foreach ($dayExercises as $exercise) {
                $this->db->insert(
                    'workout_days_has_exercises',
                    [
                        'workout_day_id' => $id,
                        'exercise_id' => $exercise['exercise_id'],
                        'sets' => $exercise['sets'],
                        'reps' => $exercise['reps'],
                        'weight' => $exercise['weight'],
                    ]
                );
            }
        }
    }

    /**
     * Remove linked exercises records.
     *
     * @param int $id Day id
     *
     * @return int Result
     */
    protected function removeLinkedExercises($id)
    {
        $exercisesIds = $this->findLinkedExercisesIds($id);

        $result = $this->db->delete('workout_days_has_exercises', ['workout_day_id' => $id]);

        foreach ($exercisesIds as $exerciseId) {
            $this->exerciseRepository->delete($exerciseId);
        }

        return $result;
    }

    /**
     * Convert day number to day name.
     *
     * @param int $number Day number
     *
     * @return mixed
     */
    protected function convertNumberToDay($number)
    {
        $days = ['monday', 'thursday', 'wednesday', 'tuesday', 'friday', 'saturday', 'sunday'];

        return $days[$number];
    }

    /**
     * Find linked exercises by day id.
     *
     * @param int $id Element id
     *
     * @return array Result
     */
    protected function findLinkedExercises($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('de.exercise_id', 'de.sets', 'de.reps', 'de.weight')
            ->from('workout_days_has_exercises', 'de')
            ->where('de.workout_day_id = :day_id')
            ->setParameter(':day_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        if ($result) {
            foreach ($result as &$exercise) {
                $exercise = $exercise + $this->exerciseRepository->findOneById($exercise['exercise_id']);
            }
            unset($exercise);
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

        return $queryBuilder->select('wd.workout_day_id', 'wd.weekday')
            ->from('workout_days', 'wd');
    }
}
