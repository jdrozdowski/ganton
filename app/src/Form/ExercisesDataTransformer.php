<?php
/**
 * Exercises data transformer.
 */
namespace Form;

use Repository\ExerciseRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ExercisesDataTransformer
 *
 * @package Form
 */
class ExercisesDataTransformer implements DataTransformerInterface
{
    /**
     * Exercise repository.
     *
     * @var ExerciseRepository|null $exerciseRepository
     */
    protected $exerciseRepository = null;

    /**
     * ExercisesDataTransformer constructor.
     *
     * @param ExerciseRepository $exerciseRepository Exercise repository
     */
    public function __construct(ExerciseRepository $exerciseRepository)
    {
        $this->exerciseRepository = $exerciseRepository;
    }

    /**
     * Transform array of exercises Ids to string of names.
     *
     * @param mixed $exercises
     *
     * @return array|mixed
     */
    public function transform($exercises)
    {
        if (null == $exercises) {
            return [];
        }

        foreach ($exercises as &$exercise) {
            unset($exercise['exercise_id']);
            unset($exercise['record']);
        }
        unset($exercise);

        return $exercises;
    }

    /**
     * Transform string of exercise names into array of exercises Ids.
     *
     * @param mixed $exerciseDetails
     *
     * @return array
     */
    public function reverseTransform($exerciseDetails)
    {
        if (!$exerciseDetails) {
            return [];
        }

        foreach ($exerciseDetails as $data) {
            $exercise = $this->exerciseRepository->findOneByName($data['name']);
            if (null === $exercise || !count($exercise)) {
                $exercise['name'] = $data['name'];
                $exercise['record'] = $data['weight'];
                $exercise = $this->exerciseRepository->save($exercise);
            } else {
                if ($data['weight'] > 0 && $data['weight'] > $exercise['record']) {
                    $this->exerciseRepository->saveRecord($exercise['exercise_id'], $data['weight']);
                }
            }
            $exercise = $exercise + $data;
            $exercises[] = $exercise;
        }

        return isset($exercises) ? $exercises : [];
    }
}
