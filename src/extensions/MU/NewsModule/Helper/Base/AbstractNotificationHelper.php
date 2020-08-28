<?php

/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 *
 * @see https://homepages-mit-zikula.de
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

declare(strict_types=1);

namespace MU\NewsModule\Helper\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\WorkflowHelper;

/**
 * Notification helper base class.
 */
abstract class AbstractNotificationHelper
{
    use TranslatorTrait;
    
    /**
     * @var RouterInterface
     */
    protected $router;
    
    /**
     * @var ZikulaHttpKernelInterface
     */
    protected $kernel;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var Environment
     */
    protected $twig;
    
    /**
     * @var MailerInterface
     */
    protected $mailer;
    
    /**
     * @var LoggerInterface
     */
    protected $mailLogger;
    
    /**
     * @var bool
     */
    protected $mailLoggingEnabled;
    
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;
    
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;
    
    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;
    
    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;
    
    /**
     * List of notification recipients.
     *
     * @var array $recipients
     */
    protected $recipients = [];
    
    /**
     * Which type of recipient is used ("creator", "moderator" or "superModerator").
     *
     * @var string recipientType
     */
    protected $recipientType = '';
    
    /**
     * The entity which has been changed before.
     *
     * @var EntityAccess entity
     */
    protected $entity = '';
    
    /**
     * Name of workflow action which is being performed.
     *
     * @var string action
     */
    protected $action = '';
    
    /**
     * Name of the application.
     *
     * @var string
     */
    protected $name;
    
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        Environment $twig,
        MailerInterface $mailer,
        LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        GroupRepositoryInterface $groupRepository,
        UserRepositoryInterface $userRepository,
        EntityDisplayHelper $entityDisplayHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->kernel = $kernel;
        $this->setTranslator($translator);
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailLogger = $mailLogger;
        $this->mailLoggingEnabled = $variableApi->get('ZikulaMailerModule', 'enableLogging', false);
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->workflowHelper = $workflowHelper;
        $this->name = 'MUNewsModule';
    }
    
    /**
     * Sends a mail to either an item's creator or a group of moderators.
     */
    public function process(array $args): bool
    {
        if (!isset($args['recipientType']) || !$args['recipientType']) {
            return false;
        }
    
        if (!isset($args['action']) || !$args['action']) {
            return false;
        }
    
        if (!isset($args['entity']) || !$args['entity']) {
            return false;
        }
    
        $this->recipientType = $args['recipientType'];
        $this->action = $args['action'];
        $this->entity = $args['entity'];
    
        $debug = isset($args['debug']) && $args['debug'];
        $this->collectRecipients($debug);
    
        if (!count($this->recipients)) {
            return true;
        }
    
        $result = $this->sendMails();
    
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request && $request->hasSession() ? $request->getSession() : null;
        if (null !== $session) {
            $session->remove($this->name . 'AdditionalNotificationRemarks');
        }
    
        return $result;
    }
    
    /**
     * Collects the recipients.
     *
     * @param bool $debug Whether to add the admin or not
     */
    protected function collectRecipients(bool $debug = false)
    {
        $this->recipients = [];
    
        if (in_array($this->recipientType, ['moderator', 'superModerator'], true)) {
            $modVarSuffixes = [
                'message' => 'Messages',
            ];
            $modVarSuffix = $modVarSuffixes[$this->entity->get_objectType()];
    
            $moderatorGroupId = $this->variableApi->get(
                'MUNewsModule',
                'moderationGroupFor' . $modVarSuffix,
                GroupsConstant::GROUP_ID_ADMIN
            );
            if ('superModerator' === $this->recipientType) {
                $moderatorGroupId = $this->variableApi->get(
                    'MUNewsModule',
                    'superModerationGroupFor' . $modVarSuffix,
                    GroupsConstant::GROUP_ID_ADMIN
                );
            }
    
            $moderatorGroup = $this->groupRepository->find($moderatorGroupId);
            if (null !== $moderatorGroup) {
                foreach ($moderatorGroup['users'] as $user) {
                    $this->addRecipient($user);
                }
            }
        } elseif ('creator' === $this->recipientType && method_exists($this->entity, 'getCreatedBy')) {
            $this->addRecipient($this->entity->getCreatedBy());
        } elseif ($this->usesDesignatedEntityFields()) {
            $this->addRecipient();
        }
    
        if ($debug) {
            // add the admin, too
            $this->addRecipient($this->userRepository->find(UsersConstant::USER_ID_ADMIN));
        }
    }
    
    /**
     * Collects data for building the recipients array.
     */
    protected function addRecipient(UserEntity $user = null): void
    {
        if ($this->usesDesignatedEntityFields()) {
            $recipientTypeParts = explode('-', $this->recipientType);
            if (2 !== count($recipientTypeParts)) {
                return;
            }
            $fieldNames = explode('^', $recipientTypeParts[1]);
            if (2 !== count($fieldNames)) {
                return;
            }
    
            $this->recipients[] = [
                'name' => $this->entity[$fieldNames[1]],
                'email' => $this->entity[$fieldNames[0]],
            ];
    
            return;
        }
    
        if (null === $user) {
            return;
        }
    
        $userAttributes = $user->getAttributes();
        $recipientName = isset($userAttributes['name']) && !empty($userAttributes['name'])
            ? $userAttributes['name']
            : $user->getUname()
        ;
        $this->recipients[] = [
            'name' => $recipientName,
            'email' => $user->getEmail(),
        ];
    }
    
    /**
     * Performs the actual mailing.
     */
    protected function sendMails(): bool
    {
        $objectType = $this->entity->get_objectType();
        $siteName = $this->variableApi->getSystemVar('sitename');
        $adminMail = $this->variableApi->getSystemVar('adminmail');
    
        $templateType = '';
        if ($this->usesDesignatedEntityFields()) {
            $templateType = $this->recipientType;
        } else {
            $templateType = 'creator' === $this->recipientType ? 'Creator' : 'Moderator';
        }
        $template = 'Email/notify' . ucfirst($objectType) . $templateType . '.html.twig';
    
        $mailData = $this->prepareEmailData();
        $subject = $this->getMailSubject();
    
        // send one mail per recipient
        try {
            foreach ($this->recipients as $recipient) {
                if (!isset($recipient['name']) || !$recipient['name']) {
                    continue;
                }
                if (!isset($recipient['email']) || !$recipient['email']) {
                    continue;
                }
        
                $body = $this->twig->render('@MUNewsModule/' . $template, [
                    'recipient' => $recipient,
                    'mailData' => $mailData,
                ]);
        
                $email = (new Email())
                    ->from(new Address($adminMail, $siteName))
                    ->to(new Address($recipient['email'], $recipient['name']))
                    ->subject($subject)
                    ->html($body)
                ;    
        
                $this->mailer->send($email);
        
                if ($this->mailLoggingEnabled) {
                    $this->mailLogger->info(sprintf('Email sent to %s', $recipient['email']), [
                        'in' => __METHOD__
                    ]);
                }
            }
        } catch (TransportExceptionInterface $exception) {
            $this->mailLogger->error($exception->getMessage(), [
                'in' => __METHOD__
            ]);
        
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the subject used for the emails to be sent.
     */
    protected function getMailSubject(): string
    {
        $mailSubject = '';
        if (
            in_array($this->recipientType, ['moderator', 'superModerator'], true)
            || $this->usesDesignatedEntityFields()
        ) {
            if ('submit' === $this->action) {
                $mailSubject = $this->trans('New content has been submitted', [], 'mail');
            } elseif ('demote' === $this->action) {
                $mailSubject = $this->trans('Content has been demoted', [], 'mail');
            } elseif ('accept' === $this->action) {
                $mailSubject = $this->trans('Content has been accepted', [], 'mail');
            } elseif ('approve' === $this->action) {
                $mailSubject = $this->trans('Content has been approved', [], 'mail');
            } elseif ('delete' === $this->action) {
                $mailSubject = $this->trans('Content has been deleted', [], 'mail');
            } else {
                $mailSubject = $this->trans('Content has been updated', [], 'mail');
            }
        } elseif ('creator' === $this->recipientType) {
            if ('accept' === $this->action) {
                $mailSubject = $this->trans('Your submission has been accepted', [], 'mail');
            } elseif ('approve' === $this->action) {
                $mailSubject = $this->trans('Your submission has been approved', [], 'mail');
            } elseif ('reject' === $this->action) {
                $mailSubject = $this->trans('Your submission has been rejected', [], 'mail');
            } elseif ('delete' === $this->action) {
                $mailSubject = $this->trans('Your submission has been deleted', [], 'mail');
            } else {
                $mailSubject = $this->trans('Your submission has been updated', [], 'mail');
            }
        }
    
        return $mailSubject;
    }
    
    /**
     * Collects data used by the email templates.
     */
    protected function prepareEmailData(): array
    {
        $objectType = $this->entity->get_objectType();
        $state = $this->entity->getWorkflowState();
        $stateInfo = $this->workflowHelper->getStateInfo($state);
    
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request && $request->hasSession() ? $request->getSession() : null;
        $remarks = null !== $session ? $session->get($this->name . 'AdditionalNotificationRemarks', '') : '';
    
        $hasDisplayAction = in_array($objectType, ['message'], true);
        $hasEditAction = in_array($objectType, ['message'], true);
        $routeArea = in_array($this->recipientType, ['moderator', 'superModerator'], true) ? 'admin' : '';
        $routePrefix = 'munewsmodule_' . strtolower($objectType) . '_' . $routeArea;
    
        $urlArgs = $this->entity->createUrlArgs();
        $displayUrl = $hasDisplayAction
            ? $this->router->generate($routePrefix . 'display', $urlArgs, UrlGeneratorInterface::ABSOLUTE_URL)
            : ''
        ;
    
        $needsArg = in_array($objectType, ['message'], true);
        $urlArgs = $needsArg ? $this->entity->createUrlArgs(true) : $this->entity->createUrlArgs();
        $editUrl = $hasEditAction
            ? $this->router->generate($routePrefix . 'edit', $urlArgs, UrlGeneratorInterface::ABSOLUTE_URL)
            : ''
        ;
    
        return [
            'name' => $this->entityDisplayHelper->getFormattedTitle($this->entity),
            'newState' => $stateInfo['text'],
            'remarks' => $remarks,
            'editor' => $this->getEditorName(),
            'displayUrl' => $displayUrl,
            'editUrl' => $editUrl,
        ];
    }
    
    /**
     * Checks whether a special notification type is used or not.
     */
    protected function usesDesignatedEntityFields(): bool
    {
        return 0 === mb_strpos($this->recipientType, 'field-');
    }
    
    /**
     * Determines name of editor for the given entity.
     */
    protected function getEditorName(): string
    {
        if (!in_array($this->entity->get_objectType(), ['message', 'image'], true)) {
            return '';
        }
    
        return $this->entity->getUpdatedBy()->getUname();
    }
}
