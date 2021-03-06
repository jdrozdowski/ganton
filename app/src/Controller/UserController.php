<?php
/**
 * User controller.
 */
namespace Controller;

use Form\RegistrationType;
use Form\UserPasswordType;
use Form\UserType;
use Repository\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 *
 * @package Controller
 */
class UserController implements ControllerProviderInterface
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
        $controller->match('/', [$this, 'indexAction'])
            ->bind('user_index');
        $controller->match('/{role}', [$this, 'indexAction'])
            ->value('type', 'all')
            ->method('POST|GET')
            ->assert('role', 'all|athlete|coach')
            ->bind('user_search');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('user_view');
        $controller->match('/edit', [$this, 'editAction'])
            ->method('POST|GET')
            ->bind('user_edit');
        $controller->match('/changepassword', [$this, 'changePasswordAction'])
            ->method('POST|GET')
            ->bind('user_change_password');
        $controller->match('/register', [$this, 'registerAction'])
            ->method('POST|GET')
            ->bind('user_register');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param string                                    $role    User role
     *
     * @return mixed
     */
    public function indexAction(Application $app, Request $request, $role = 'all')
    {
        $userRepository = new UserRepository($app['db']);

        $form = $app['form.factory']->createBuilder()->add('search', SearchType::class, ['label' => 'label.search'])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $string = $form->get('search')->getData();

            $users = $userRepository->findAll($role, $string);
        } else {
            $users = $userRepository->findAll($role);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($users, $page, $limit);

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'role' => $role,
                'users' => $pagination,
                'form' => $form->createView(),
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
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);

        if (!$user) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('user_index'));
        }

        return $app['twig']->render(
            'user/view.html.twig',
            ['user' => $user]
        );
    }

    /**
     * Register action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function registerAction(Application $app, Request $request)
    {
        if ($app['security.token_storage']->getToken()->getUser() !== 'anon.') {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $user = [];

        $form = $app['form.factory']->createBuilder(
            RegistrationType::class,
            $user,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = array_slice($form->getData(), 0, 2);
            $userData = array_slice($form->getData(), 2);

            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['plainPassword'], '');
            unset($user['plainPassword']);

            $user['role_id'] = 3;

            $userRepository = new UserRepository($app['db']);
            $userRepository->save($user, $userData);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('auth_login'), 301);
        }

        return $app['twig']->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function editAction(Application $app, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findEditableDataById($app['security.token_storage']->getToken()->getUser()->getId());

        if (!$user) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('user_index'));
        }
        $user['user_id'] = $app['security.token_storage']->getToken()->getUser()->getId();

        $form = $app['form.factory']->createBuilder(
            UserType::class,
            $user
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userRepository->save($user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('user_view', ['id' => $user['user_id']]), 301);
        }

        return $app['twig']->render(
            'user/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Change password action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP response
     */
    public function changePasswordAction(Application $app, Request $request)
    {
        $userRepository = new UserRepository($app['db']);

        if (!$userRepository->findOneById($app['security.token_storage']->getToken()->getUser()->getId())) {
            $app['session']->getFlashBag()->add(
                'massages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $id = $app['security.token_storage']->getToken()->getUser()->getId();

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

            return $app->redirect($app['url_generator']->generate('user_view', ['id' => $id]), 301);
        }

        return $app['twig']->render(
            'user/password.html.twig',
            [
                'user_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }
}
