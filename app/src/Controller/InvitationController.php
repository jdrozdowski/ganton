<?php
/**
 * Invitation controller.
 */
namespace Controller;

use Repository\InvitationRepository;
use Repository\WorkoutRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvitationController
 *
 * @package Controller
 */
class InvitationController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return array
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])
            ->bind('invitation_index');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('invitation_delete');
        $controller->match('/{id}/accept', [$this, 'acceptAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('invitation_accept');

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
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
            $userId = $token->getUser()->getId();

            $invitationRepository = new InvitationRepository($app['db']);
            $invitations = $invitationRepository->findAll($userId);
        } else {
            return $app->redirect($app['url_generator']->generate('auth_login'), 301);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $pagination = $app['knp_paginator']->paginate($invitations, $page, $limit);

        return $app['twig']->render(
            'invitation/index.html.twig',
            [
                'invitations' => $pagination,
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
        $invitationRepository = new invitationRepository($app['db']);
        $invitation = $invitationRepository->findOneById($id, $app['security.token_storage']->getToken()->getUser()->getId());

        if (!$invitation) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('invitation_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $invitation)->add(
            'invitation_id',
            HiddenType::class
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitationRepository->delete($id);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect($app['url_generator']->generate('invitation_index'), 301);
        }

        return $app['twig']->render(
            'invitation/delete.html.twig',
            [
                'invitation_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Accept action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Element Id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse HTTP Response
     */
    public function acceptAction(Application $app, $id, Request $request)
    {
        $invitationRepository = new invitationRepository($app['db']);
        $invitation = $invitationRepository->findOneById($id, $app['security.token_storage']->getToken()->getUser()->getId());

        if (!$invitation) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('invitation_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class)->add(
            'invitation_id',
            HiddenType::class
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workoutRepository = new WorkoutRepository($app['db']);
            $workout = $workoutRepository->findOneById($invitation['from_user_id'], $invitation['workout_id']);
            unset($workout['workout_id']);
            $workoutRepository->save($workout, $app['security.token_storage']->getToken()->getUser()->getId());

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
            'invitation/accept.html.twig',
            [
                'invitation_id' => $id,
                'form' => $form->createView(),
            ]
        );
    }
}
