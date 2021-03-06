<?php
/**
 * Workout controller.
 */
namespace Controller;

use Form\UsernameType;
use Form\WorkoutType;
use Repository\ExerciseRepository;
use Repository\InvitationRepository;
use Repository\UserRepository;
use Repository\WorkoutRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WorkoutController
 *
 * @package Controller
 */
class WorkoutController implements ControllerProviderInterface
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
            ->bind('workout_index');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('workout_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('workout_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('workout_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('workout_delete');
        $controller->match('/{id}/invite', [$this, 'inviteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('workout_invite');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return mixed
     */
    public function indexAction(Application $app, Request $request)
    {
        $workoutRepository = new WorkoutRepository($app['db']);
        $workouts = $workoutRepository->findAll($app['security.token_storage']->getToken()->getUser()->getId());

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($workouts, $page, $limit);

        return $app['twig']->render(
            'workout/index.html.twig',
            ['workouts' => $pagination]
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
        $workoutRepository = new WorkoutRepository($app['db']);
        $workout = $workoutRepository->findOneById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$workout) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_index'));
        }

        return $app['twig']->render(
            'workout/view.html.twig',
            ['workout' => $workout]
        );
    }

    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function addAction(Application $app, Request $request)
    {
        $workout = [];

        $form = $app['form.factory']->createBuilder(
            WorkoutType::class,
            $workout,
            ['exercise_repository' => new ExerciseRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $app['security.token_storage']->getToken();
            if (null !== $token) {
                $userId = $token->getUser()->getId();
            }
            $workoutRepository = new WorkoutRepository($app['db']);
            $workoutRepository->save($form->getData(), $userId);

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
            'workout/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param string                                    $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function editAction(Application $app, $id, Request $request)
    {
        $workoutRepository = new WorkoutRepository($app['db']);
        $workout = $workoutRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$workout) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_index'));
        }

        $form = $app['form.factory']->createBuilder(
            WorkoutType::class,
            $workout,
            ['exercise_repository' => new ExerciseRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workoutRepository->save($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_view', ['id' => $workout['workout_id']]), 301);
        }

        return $app['twig']->render(
            'workout/edit.html.twig',
            [
                'workout' => $workout,
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
        $workoutRepository = new WorkoutRepository($app['db']);
        $workout = $workoutRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$workout) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $workout)->add(
            'workout_id',
            HiddenType::class
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workoutRepository->delete($form->get('workout_id')->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_index'), 301);
        }

        return $app['twig']->render(
            'workout/delete.html.twig',
            [
                'workout' => $workout,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Invite action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function inviteAction(Application $app, $id, Request $request)
    {
        $user = [];

        $form = $app['form.factory']->createBuilder(
            UsernameType::class,
            $user,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $app['security.token_storage']->getToken();
            if (null !== $token) {
                $invitationRepository = new invitationRepository($app['db']);
                $invitation['from_user_id'] = $token->getUser()->getId();
                $invitation['to_user_id'] = $form->get('user_id')->getData();
                $invitation['workout_id'] = $id;
                $invitationRepository->save($invitation);
            }

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_send',
                ]
            );

            return $app->redirect($app['url_generator']->generate('workout_view', ['id' => $id]), 301);
        }

        return $app['twig']->render(
            'workout/invite.html.twig',
            [
                'workout_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }
}
