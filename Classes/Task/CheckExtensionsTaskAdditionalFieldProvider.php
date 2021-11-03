<?php
namespace PeterBenke\PbCheckExtensions\Task;

/**
 * PbCheckExtensions
 */
use PeterBenke\PbCheckExtensions\Utility\StringUtility as PBStringUtility;

/**
 * TYPO3
 */
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * class CheckExtensionsTaskAdditionalFieldProvider
 */
class CheckExtensionsTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
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
				$taskInfo['emailSubject'] = $task->emailSubject;
			}
		}

		if (empty($taskInfo['emailMailFrom'])) {
			if ($cmd == 'add') {
				$taskInfo['emailMailFrom'] = '';
			} else {
				$taskInfo['emailMailFrom'] = $task->emailMailFrom;
			}
		}

		if (empty($taskInfo['emailMailTo'])) {
			if ($cmd == 'add') {
				$taskInfo['emailMailTo'] = '';
			} else {
				$taskInfo['emailMailTo'] = $task->emailMailTo;
			}
		}

		if (empty($taskInfo['excludeExtensionsFromCheck'])) {
			if ($cmd == 'add') {
				$taskInfo['excludeExtensionsFromCheck'] = '';
			} else {
				$taskInfo['excludeExtensionsFromCheck'] = $task->excludeExtensionsFromCheck;
			}
		}

		// Input fields
		return [

			'task_emailSubject' => [
				'code' => '<input type="text" name="tx_scheduler[emailSubject]" id="task_emailSubject" value="' . $taskInfo['emailSubject'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailSubject.label')
			],

			'task_emailMailFrom' => [
				'code' => '<input type="text" name="tx_scheduler[emailMailFrom]" id="task_emailMailFrom" value="' . $taskInfo['emailMailFrom'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailFrom.label')
			],

			'task_emailMailTo' => [
				'code' => '<input type="text" name="tx_scheduler[emailMailTo]" id="task_emailMailTo" value="' . $taskInfo['emailMailTo'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailTo.label')
			],

			'task_excludeExtensionsFromCheck' => [
				'code' => '<textarea name="tx_scheduler[excludeExtensionsFromCheck]" id="task_excludeExtensionsFromCheck">' . $taskInfo['excludeExtensionsFromCheck'] . '</textarea>',
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
		if(empty($submittedData['emailSubject'])){
			$ok = false;
			$errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailSubject.validation');
		}

		// Validate email from
		if(empty($submittedData['emailMailFrom']) || filter_var($submittedData['emailMailFrom'], FILTER_VALIDATE_EMAIL) === FALSE) {
			$ok = false;
			$errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailMail.validation') . ': ' . $submittedData['emailMailFrom'];
		}

		// Validate email to addresses
		$emailMailTos = PBStringUtility::explodeAndTrim(',', $submittedData['emailMailTo']);

		foreach($emailMailTos as $emailMailTo){
			if(empty($emailMailTo) || filter_var($emailMailTo, FILTER_VALIDATE_EMAIL) === FALSE) {
				$ok = false;
				$errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailMail.validation') . ': ' . $emailMailTo;
			}
		}

		if($ok){
			return true;
		}

		$message = implode(' / ', $errorMessages);
		$schedulerModule->__call('addMessage', [$message, AbstractMessage::ERROR]);
		return false;

	}

	/**
	 * Saves the input value
	 * @param array $submittedData
	 * @param AbstractTask|CheckExtensionsTask $task
	 * @author Peter Benke <info@typomotor.de>
	 */
	public function saveAdditionalFields(array $submittedData, AbstractTask $task)
	{
		$task->emailSubject = trim($submittedData['emailSubject']);
		$task->emailMailFrom = trim($submittedData['emailMailFrom']);
		$task->emailMailTo = trim($submittedData['emailMailTo']);
		$task->excludeExtensionsFromCheck = trim($submittedData['excludeExtensionsFromCheck']);
	}

	/**
	 * Translate a given string
	 * @param string $key
	 * @return string|null
	 * @author Peter Benke <info@typomotor.de>
	 */
	protected function translate(string $key): ?string
	{
		return LocalizationUtility::translate($key, 'pb_check_extensions');
	}

}
