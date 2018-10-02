<?php
/**
 * Admin controller.
 */
namespace Controller;

use Form\ManageExerciseType;
use Form\ManageUserType;
use Form\UserPasswordType;
use Repository\ExerciseRepository;
use Repository\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminController
 *
 * @package Controller
 */
class AdminController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->match('/user', [$this, 'indexUserAction' ])
            ->method('POST|GET')
            ->bind('admin_user_index');
        $controller->get('/exercise', [$this, 'indexExerciseAction' ])
            ->bind('admin_exercise_index');
        $controller->match('/user/{id}/changepassword', [$this, 'changePasswordAction'])
            ->method('POST|GET')
            ->bind('admin_user_change_password');
        $controller->match('/user/{id}/edit', [$this, 'editUserAction' ])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('admin_user_edit');
        $controller->match('/exercise/{id}/edit', [$this, 'editExerciseAction' ])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('admin_exercise_edit');

        return $controller;
    }

    /**
     * Index user action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return mixed
     */
    public function indexUserAction(Application $app, Request $request)
    {
        $userRepository = new UserRepository($app['db']);

        $form = $app['form.factory']->createBuilder()->add('search', SearchType::class, ['label' => 'label.search'])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $string = $form->get('search')->getData();

            $users = $userRepository->findAllByAdmin($string);
        } else {
            $users = $userRepository->findAllByAdmin();
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($users, $page, $limit);

        return $app['twig']->render(
            'admin/user/index.html.twig',
            [
                'users' => $pagination,
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * Index exercise action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return mixed
     */
    public function indexExerciseAction(Application $app)
    {
        $exerciseRepository = new ExerciseRepository($app['db']);

        return $app['twig']->render(
            'admin/exercise/index.html.twig',
            ['exercises' => $exerciseRepository->findAll()]
        );
    }

    /**
     * Edit user action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function editUserAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $userData = $userRepository->findNameAndSurnameAndUsernameById($id);

        if (!$userData) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_user_index'));
        }
        $user['user_id'] = $id;
        $user['login'] = $userData['login'];

        $form = $app['form.factory']->createBuilder(
            ManageUserType::class,
            $user,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->update($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_user_index'), 301);
        }

        return $app['twig']->render(
            'admin/user/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Change user password action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP response
     */
    public function changePasswordAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);

        if (!$userRepository->findNameAndSurnameAndUsernameById($id)) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_user_index'));
        }

        $form = $app['form.factory']->createBuilder(
            UserPasswordType::class
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $app['security.encoder.bcrypt']->encodePassword(
                $form->get('plainPassword')->getData(),
                ''
            );

            $userRepository->savePassword($password, $id);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_user_index'), 301);
        }

        return $app['twig']->render(
            'admin/user/password.html.twig',
            [
                'user_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit exercise action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP response
     */
    public function editExerciseAction(Application $app, $id, Request $request)
    {
        $exerciseRepository = new ExerciseRepository($app['db']);
        $exercise = $exerciseRepository->findOneById($id);

        if (!$exercise) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_exercise_index'));
        }

        $form = $app['form.factory']->createBuilder(
            ManageExerciseType::class,
            $exercise
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exercise = $form->getData();
            $exercise['exercise_id'] = $id;
            $exerciseRepository->save($exercise);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('admin_exercise_index'), 301);
        }

        return $app['twig']->render(
            'admin/exercise/edit.html.twig',
            [
                'exercise_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }
}
