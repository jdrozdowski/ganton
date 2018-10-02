<?php
/**
 * Routine controller.
 */
namespace Controller;

use Form\PeriodType;
use Form\RoutineType;
use Form\UsernameType;
use Repository\RoutineRepository;
use Repository\UserRepository;
use Repository\WorkoutRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RoutineController
 *
 * @package Controller
 */
class RoutineController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])
            ->bind('routine_index');
        $controller->get('/{type}', [$this, 'indexAction'])
            ->value('type', 'all')
            ->assert('type', 'all|mine|available')
            ->bind('routine_index');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('routine_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('routine_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('routine_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('routine_delete');
        $controller->match('/{id}/share', [$this, 'shareAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('routine_share');
        $controller->match('/{id}/assign', [$this, 'assignAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('routine_assign');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param string                                    $type
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return mixed
     */
    public function indexAction(Application $app, $type, Request $request)
    {
        $routineRepository = new RoutineRepository($app['db']);

        if ($type === 'all') {
            $routines = $routineRepository->findAll($type);
        } elseif ($type === 'mine') {
            $routines = $routineRepository->findAll($type, $app['security.token_storage']->getToken()->getUser()->getId());
        } else {
            $routines = $routineRepository->findAll($type, $app['security.token_storage']->getToken()->getUser()->getId());
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($routines, $page, $limit);

        return $app['twig']->render(
            'routine/index.html.twig',
            [
                'type' => $type,
                'routines' => $pagination,
            ]
        );
    }

    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     * @param string             $id  Element Id
     *
     * @return mixed
     */
    public function viewAction(Application $app, $id)
    {
        $routineRepository = new RoutineRepository($app['db']);
        $routine = $routineRepository->findOneById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$routine) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'));
        }

        return $app['twig']->render(
            'routine/view.html.twig',
            ['routine' => $routine]
        );
    }

    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP response
     */
    public function addAction(Application $app, Request $request)
    {
        $routine = [];

        $form = $app['form.factory']->createBuilder(RoutineType::class, $routine)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routine = $form->getData();
            $routine['author'] = $app['security.token_storage']->getToken()->getUser()->getId();

            $routineRepository = new RoutineRepository($app['db']);
            $routineId = $routineRepository->save($routine);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('day_add', ['slug' => $routineId]), 301);
        }

        return $app['twig']->render(
            'routine/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function editAction(Application $app, $id, Request $request)
    {
        $routineRepository = new RoutineRepository($app['db']);
        $routine = $routineRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$routine) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_your',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'));
        }

        $form = $app['form.factory']->createBuilder(RoutineType::class, $routine)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routineRepository->save($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_view', ['id' => $id]), 301);
        }

        return $app['twig']->render(
            'routine/edit.html.twig',
            [
                'routine' => $routine,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $routineRepository = new RoutineRepository($app['db']);
        $routine = $routineRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$routine) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_your',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $routine)->add(
            'workout_routine_id',
            HiddenType::class
        )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routineRepository->delete($form->get('workout_routine_id')->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'), 301);
        }

        return $app['twig']->render(
            'routine/delete.html.twig',
            [
                'routine' => $routine,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Share action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function shareAction(Application $app, $id, Request $request)
    {
        $routineRepository = new RoutineRepository($app['db']);

        if (!$routineRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId(), $id)) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_your',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'));
        }

        $routine['workout_routine_id'] = $id;

        $form = $app['form.factory']->createBuilder(
            UsernameType::class,
            $routine,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routineRepository->assign($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_shared',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_view', ['id' => $id]), 301);
        }

        return $app['twig']->render(
            'routine/share.html.twig',
            [
                'routine' => $routine,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Assign action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function assignAction(Application $app, $id, Request $request)
    {
        $routineRepository = new RoutineRepository($app['db']);
        $routine = $routineRepository->findOneById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$routine) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('routine_index'));
        }

        $form = $app['form.factory']->createBuilder(PeriodType::class, $routine)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routine = $form->getData();
            $workoutRepository = new WorkoutRepository($app['db']);
            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($routine['begin'], $interval, $routine['end']);

            foreach ($period as $dt) {
                foreach ($routine['days'] as $day) {
                    if (strtolower($dt->format("l")) == $day['weekday']) {
                        $workout['due_date'] = $dt->format('Y-m-d H:i:s');
                        $workout['exercises'] = $day['exercises'];

                        $workoutRepository->save($workout, $app['security.token_storage']->getToken()->getUser()->getId());
                    }
                }
            }

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_index'), 301);
        }

        return $app['twig']->render(
            'routine/assign.html.twig',
            [
                'routine_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }
}
