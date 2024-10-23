<?php

namespace PeterBenke\PbCheckExtensions\Task;

/**
 * PbCheckExtensions
 */

use PeterBenke\PbCheckExtensions\Utility\StringUtility as PBStringUtility;

/**
 * TYPO3
 */

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class CheckExtensionsTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Create additional fields
     * @param array $taskInfo
     * @param CheckExtensionsTask|AbstractTask $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     * @author Peter Benke <info@typomotor.de>
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {

        $cmd = $schedulerModule->getCurrentAction();

        if (empty($taskInfo['emailSubject'])) {
            if ($cmd == 'add') {
                $taskInfo['emailSubject'] = '';
            } else {
                $taskInfo['emailSubject'] = $task->getEmailSubject();
            }
        }

        if (empty($taskInfo['emailMailFrom'])) {
            if ($cmd == 'add') {
                $taskInfo['emailMailFrom'] = '';
            } else {
                $taskInfo['emailMailFrom'] = $task->getEmailMailFrom();
            }
        }

        if (empty($taskInfo['emailMailTo'])) {
            if ($cmd == 'add') {
                $taskInfo['emailMailTo'] = '';
            } else {
                $taskInfo['emailMailTo'] = $task->getEmailMailTo();
            }
        }

        if (empty($taskInfo['excludeExtensionsFromCheck'])) {
            if ($cmd == 'add') {
                $taskInfo['excludeExtensionsFromCheck'] = '';
            } else {
                $taskInfo['excludeExtensionsFromCheck'] = $task->getExcludeExtensionsFromCheck();
            }
        }

        // Input fields
        return [

            'task_emailSubject' => [
                'code' => '<input class="form-control" type="text" name="tx_scheduler[emailSubject]" id="task_emailSubject" value="' . $taskInfo['emailSubject'] . '" />',
                'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailSubject.label')
            ],

            'task_emailMailFrom' => [
                'code' => '<input class="form-control" type="text" name="tx_scheduler[emailMailFrom]" id="task_emailMailFrom" value="' . $taskInfo['emailMailFrom'] . '" />',
                'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailFrom.label')
            ],

            'task_emailMailTo' => [
                'code' => '<input class="form-control" type="text" name="tx_scheduler[emailMailTo]" id="task_emailMailTo" value="' . $taskInfo['emailMailTo'] . '" />',
                'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailTo.label')
            ],

            'task_excludeExtensionsFromCheck' => [
                'code' => '<textarea class="form-control" name="tx_scheduler[excludeExtensionsFromCheck]" id="task_excludeExtensionsFromCheck">' . $taskInfo['excludeExtensionsFromCheck'] . '</textarea>',
                'label' => $this->translate('task.checkExtensionsTask.fieldProvider.excludeExtensionsFromCheck.label')
            ],

        ];

    }

    /**
     * Validates the input value(s)
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @return bool
     * @author Peter Benke <info@typomotor.de>
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {

        $ok = true;
        $errorMessages = [];

        $submittedData['emailSubject'] = trim($submittedData['emailSubject']);
        $submittedData['emailMailFrom'] = trim($submittedData['emailMailFrom']);
        $submittedData['emailMailTo'] = trim($submittedData['emailMailTo']);

        // Validate email subject
        if (empty($submittedData['emailSubject'])) {
            $ok = false;
            $errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailSubject.validation');
        }

        // Validate email from
        if (empty($submittedData['emailMailFrom']) || filter_var($submittedData['emailMailFrom'], FILTER_VALIDATE_EMAIL) === FALSE) {
            $ok = false;
            $errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailMail.validation') . ': ' . $submittedData['emailMailFrom'];
        }

        // Validate email to addresses
        $emailMailTos = PBStringUtility::explodeAndTrim(',', $submittedData['emailMailTo']);

        foreach ($emailMailTos as $emailMailTo) {
            if (empty($emailMailTo) || filter_var($emailMailTo, FILTER_VALIDATE_EMAIL) === FALSE) {
                $ok = false;
                $errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailMail.validation') . ': ' . $emailMailTo;
            }
        }

        if ($ok) {
            return true;
        }

        $this->addMessage(implode(' / ', $errorMessages), ContextualFeedbackSeverity::ERROR);
        return false;

    }

    /**
     * Saves the input value
     * @param array $submittedData
     * @param CheckExtensionsTask|AbstractTask $task
     * @return void
     * @author Peter Benke <info@typomotor.de>
     */
    public function saveAdditionalFields(array $submittedData, $task): void
    {
        $task->setEmailSubject(trim($submittedData['emailSubject']));
        $task->setEmailMailFrom(trim($submittedData['emailMailFrom']));
        $task->setEmailMailTo(trim($submittedData['emailMailTo']));
        $task->setExcludeExtensionsFromCheck(trim($submittedData['excludeExtensionsFromCheck']));
    }

    /**
     * Translate a given string
     * @param string $key
     * @return string|null
     * @author Peter Benke <info@typomotor.de>
     */
    protected function translate(string $key): ?string
    {
        return LocalizationUtility::translate($key, 'PbCheckExtensions');
    }
}
