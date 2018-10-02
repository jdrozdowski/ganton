<?php
/**
 * Workout repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Class WorkoutRepository
 *
 * @package Repository
 */
class WorkoutRepository
{
    /**
     * Doctrine DBAL Connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * Exercise repository.
     *
     * @var null|\Repository\ExerciseRepository $exerciseRepository
     */
    protected $exerciseRepository = null;

    /**
     * Comment repository.
     *
     * @var null|\Repository\CommentRepository $commentRepository
     */
    protected $commentRepository = null;

    /**
     * WorkoutRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->exerciseRepository = new ExerciseRepository($db);
        $this->commentRepository = new CommentRepository($db);
    }

    /**
     * Authorization.
     *
     * @param int      $userId User Id
     * @param null|int $id     Element Id
     *
     * @return array Result
     */
    public function authorize($userId, $id = null)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('uw.workout_id')
            ->from('users_has_workouts', 'uw')
            ->where('uw.user_id = :user_id')
            ->setParameter(':user_id', $userId, \PDO::PARAM_INT);

        if (null !== $id) {
            $queryBuilder->andWhere('uw.workout_id = :id')
                ->setParameter(':id', $id, \PDO::PARAM_INT);
        }

        $result = $queryBuilder->execute()->fetchAll();

        return $result ? array_column($result, 'workout_id') : [];
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
        $ids = $this->authorize($userId);

        if ($ids) {
            $queryBuilder = $this->queryIdAndDueDate();
            $queryBuilder->where('w.workout_id IN (:ids)')
                ->orderBy('w.due_date', 'DESC')
                ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

            return $queryBuilder->execute()->fetchAll();
        }

        return [];
    }

    /**
     * Fetch due date by id.
     *
     * @param int $id Element id
     *
     * @return mixed Result
     */
    public function findDueDateById($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('w.due_date')
            ->from('workouts', 'w')
            ->where('w.workout_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        return $queryBuilder->execute()->fetch();
    }

    /**
     * Fetch one record with editable data.
     *
     * @param int    $userId User Id
     * @param string $id     Element Id
     *
     * @return mixed Result
     */
    public function findEditableDataById($userId, $id)
    {
        if (!$this->authorize($userId, $id)) {
            return [];
        }

        $queryBuilder = $this->queryIdAndDueDate();
        $queryBuilder->where('workout_id = :workout_id')
            ->setParameter(':workout_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        if ($result) {
            $result['exercises'] = $this->findLinkedExercises($result['workout_id']);
        }

        return $result;
    }

    /**
     * Fetch if today.
     *
     * @param int $userId User Id
     *
     * @return array Result
     */
    public function findIfToday($userId)
    {
        $queryBuilder = $this->queryIdAndDueDate();
        $queryBuilder->join('w', 'users_has_workouts', 'uw', 'w.workout_id = uw.workout_id')
            ->where('DATE(w.due_date) = CURRENT_DATE()')
            ->andWhere('uw.user_id = :user_id')
            ->setParameter(':user_id', $userId);

        $result = $queryBuilder->execute()->fetchAll();

        return $result ? $result : [];
    }

    /**
     * Fetch one record.
     *
     * @param int    $userId User Id
     * @param string $id     Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($userId, $id)
    {
        if (!$this->authorize($userId, $id)) {
            return [];
        }

        $queryBuilder = $this->queryAll();
        $queryBuilder->where('workout_id = :workout_id')
            ->setParameter(':workout_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        if ($result) {
            $result['exercises'] = $this->findLinkedExercises($result['workout_id']);
            $result['comments'] = $this->findLinkedComments($result['workout_id']);
        }

        return $result;
    }

    /**
     * Delete record.
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
            $this->removeLinkedComments($id);
            $this->removeLinkedUser($id);
            $this->db->delete('workouts', ['workout_id' => $id]);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Save record.
     *
     * @param array    $workout Workout
     * @param null|int $userId  User Id
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($workout, $userId = null)
    {
        $this->db->beginTransaction();

        try {
            $workoutExercises = isset($workout['exercises']) ? $workout['exercises'] : [];
            unset($workout['exercises'], $workout['comments']);

            $workout = $this->countSeriesRepsAndWeight($workout, $workoutExercises);

            if (isset($workout['workout_id']) && ctype_digit((string) $workout['workout_id'])) {
                $workoutId = $workout['workout_id'];
                unset($workout['workout_id']);
                $this->removeLinkedExercises($workoutId);
                $this->addLinkedExercises($workoutId, $workoutExercises);
                $this->db->update('workouts', $workout, ['workout_id' => $workoutId]);
            } else {
                $this->db->insert('workouts', $workout);
                $workoutId = $this->db->lastInsertId();
                $this->addLinkedUser($workoutId, $userId);
                $this->addLinkedExercises($workoutId, $workoutExercises);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add linked exercises.
     *
     * @param int $id Element Id
     * @param array $exercises Exercises
     */
    protected function addLinkedExercises($id, $exercises)
    {
        if ($exercises) {
            foreach ($exercises as $exercise) {
                $this->db->insert(
                    'workouts_has_exercises',
                    [
                        'workout_id' => $id,
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
    /**
     * Add linked user.
     *
     * @param int $id Element Id
     * @param int $userId User Id
     *
     * @return int Result
     */
    protected function addLinkedUser($id, $userId)
    {
        return $this->db->insert('users_has_workouts', ['user_id' => $userId, 'workout_id' => $id]);
    }

    /**
     * Count series, reps and weight.
     *
     * @param array $workout Workout
     * @param array $exercises Exercises
     *
     * @return mixed Result
     */
    protected function countSeriesRepsAndWeight($workout, $exercises)
    {
        $sets = 0;
        $reps = 0;
        $weight = 0;

        foreach ($exercises as $exercise) {
            $sets += $exercise['sets'];
            $reps += $exercise['reps'];
            $weight += $exercise['weight'];
        }

        $workout['sets_amount'] = $sets;
        $workout['reps_amount'] = $reps;
        $workout['weight_amount'] = $weight;

        return $workout;
    }

    /**
     * Find linked comments.
     *
     * @param int $id Element Id
     *
     * @return array Result
     */
    protected function findLinkedComments($id)
    {
        return $this->commentRepository->findOneByWorkoutId($id);
    }

    /**
     * Find linked exercises.
     *
     * @param int $id Workout Id
     *
     * @return array Result
     */
    protected function findLinkedExercises($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('we.exercise_id', 'we.sets', 'we.reps', 'we.weight')
            ->from('workouts_has_exercises', 'we')
            ->where('we.workout_id = :workout_id')
            ->setParameter(':workout_id', $id, \PDO::PARAM_INT);

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
     * Fetch linked comments ids.
     *
     * @param int $id Element Id
     *
     * @return array Result
     */
    protected function findLinkedCommentsIds($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('wc.comment_id')
            ->from('workout_comments', 'wc')
            ->where('wc.workout_id = :workout_id')
            ->setParameter(':workout_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? array_column($result, 'comment_id') : [];
    }

    /**
     * Fetch linked exercises ids.
     *
     * @param int $id Element Id
     *
     * @return array Result
     */
    protected function findLinkedExercisesIds($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('we.exercise_id')
            ->from('workouts_has_exercises', 'we')
            ->where('we.workout_id = :workout_id')
            ->setParameter(':workout_id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? array_column($result, 'exercise_id') : [];
    }

    /**
     * Remove linked comments.
     *
     * @param int $id Element Id
     */
    protected function removeLinkedComments($id)
    {
        $commentsIds = $this->findLinkedCommentsIds($id);

        foreach ($commentsIds as $commentId) {
            $this->commentRepository->delete($commentId);
        }
    }

    /**
     * Remove linked exercises.
     *
     * @param int $id Element Id
     *
     * @return int Result
     */
    protected function removeLinkedExercises($id)
    {
        $exercisesIds = $this->findLinkedExercisesIds($id);

        $result = $this->db->delete('workouts_has_exercises', ['workout_id' => $id]);

        foreach ($exercisesIds as $exerciseId) {
            $this->exerciseRepository->delete($exerciseId);
        }

        return $result;
    }

    /**
     * Remove linked user.
     *
     * @param int $id Element Id
     *
     * @return int Result
     */
    protected function removeLinkedUser($id)
    {
        return $this->db->delete('users_has_workouts', ['workout_id' => $id]);
    }

    /**
     * Query id and due date.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryIdAndDueDate()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('w.workout_id', 'w.due_date')
            ->from('workouts', 'w');
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('w.workout_id', 'w.due_date', 'w.sets_amount', 'w.reps_amount', 'w.weight_amount')
            ->from('workouts', 'w')
            ->orderBy('w.due_date');
    }
}
