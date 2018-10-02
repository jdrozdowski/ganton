<?php
/**
 * Exercise controller.
 */
namespace Controller;

use Repository\ExerciseRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

/**
 * Class ExerciseController
 *
 * @package Controller
 */
class ExerciseController implements ControllerProviderInterface
{
    /**
     * Routing settings
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])
            ->bind('exercise_index');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return mixed
     */
    public function indexAction(Application $app)
    {
        $exerciseRepository = new ExerciseRepository($app['db']);

        return $app['twig']->render(
            'admin/exercise/index.html.twig',
            ['exercises' => $exerciseRepository->findAll()]
        );
    }
}
