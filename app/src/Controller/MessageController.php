<?php
/**
 * Message controller.
 */
namespace Controller;

use Form\MessageType;
use Repository\MessageRepository;
use Repository\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MessageController
 *
 * @package Controller
 */
class MessageController implements ControllerProviderInterface
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
        $controller->get('/', [$this, 'indexAction'])
            ->bind('message_index');
        $controller->get('/{type}', [$this, 'indexAction'])
            ->value('type', 'received')
            ->assert('type', 'received|sent')
            ->bind('message_index');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('message_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('message_add');

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
        $messageRepository = new MessageRepository($app['db']);

        if ($type === 'received') {
            $messages = $messageRepository->findReceived($app['security.token_storage']->getToken()->getUser()->getId());
        } else {
            $messages = $messageRepository->findSent($app['security.token_storage']->getToken()->getUser()->getId());
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($messages, $page, $limit);

        return $app['twig']->render(
            'message/index.html.twig',
            [
                'type' => $type,
                'messages' => $pagination,
            ]
        );
    }

    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @param string             $id  Element Id
     *
     * @return mixed
     */
    public function viewAction(Application $app, $id)
    {
        $messageRepository = new MessageRepository($app['db']);
        $message = $messageRepository->findOneById($app['security.token_storage']->getToken()->getUser()->getId(), $id);

        if (!$message) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('message_index'));
        }

        return $app['twig']->render(
            'message/view.html.twig',
            ['message' => $message]
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
        $message = [];

        $form = $app['form.factory']->createBuilder(
            MessageType::class,
            $message,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository = new MessageRepository($app['db']);
            $message = $form->getData();
            $message['from_user_id'] = $app['security.token_storage']->getToken()->getUser()->getId();
            $messageRepository->save($message);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_sent',
                ]
            );

            return $app->redirect($app['url_generator']->generate('message_index'), 301);
        }

        return $app['twig']->render(
            'message/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
