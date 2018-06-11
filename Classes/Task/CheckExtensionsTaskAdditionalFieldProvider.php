<?php
namespace PeterBenke\PbCheckExtensions\Task;

/**
 * Class CheckExtensionsTaskAdditionalFieldProvider
 * @package PeterBenke\PbCheckExtensions\Task
 * @author Peter Benke <info@typomotor.de>
 */
class CheckExtensionsTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * Create additional fields
	 * @param array $taskInfo
	 * @param \PeterBenke\PbCheckExtensions\Task\CheckExtensionsTask $task
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject
	 * @return array
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

		if (empty($taskInfo['emailSubject'])) {
			if ($parentObject->CMD == 'add') {
				$taskInfo['emailSubject'] = '';
			} else {
				$taskInfo['emailSubject'] = $task->emailSubject;
			}
		}

		if (empty($taskInfo['emailMailFrom'])) {
			if ($parentObject->CMD == 'add') {
				$taskInfo['emailMailFrom'] = '';
			} else {
				$taskInfo['emailMailFrom'] = $task->emailMailFrom;
			}
		}

		if (empty($taskInfo['emailMailTo'])) {
			if ($parentObject->CMD == 'add') {
				$taskInfo['emailMailTo'] = '';
			} else {
				$taskInfo['emailMailTo'] = $task->emailMailTo;
			}
		}

		if (empty($taskInfo['excludeExtensionsFromCheck'])) {
			if ($parentObject->CMD == 'add') {
				$taskInfo['excludeExtensionsFromCheck'] = '';
			} else {
				$taskInfo['excludeExtensionsFromCheck'] = $task->excludeExtensionsFromCheck;
			}
		}


		// Inputfields
		$additionalFields = array(

			'task_emailSubject' => array(
				'code' => '<input type="text" name="tx_scheduler[emailSubject]" id="task_emailSubject" value="' . $taskInfo['emailSubject'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailSubject.label')
			),

			'task_emailMailFrom' => array(
				'code' => '<input type="text" name="tx_scheduler[emailMailFrom]" id="task_emailMailFrom" value="' . $taskInfo['emailMailFrom'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailFrom.label')
			),

			'task_emailMailTo' => array(
				'code' => '<input type="text" name="tx_scheduler[emailMailTo]" id="task_emailMailTo" value="' . $taskInfo['emailMailTo'] . '" />',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.emailMailTo.label')
			),

			'task_excludeExtensionsFromCheck' => array(
				// 'code' => '<input type="text" name="tx_scheduler[excludeExtensionsFromCheck]" id="task_excludeExtensionsFromCheck" value="' . $taskInfo['excludeExtensionsFromCheck'] . '" />',
				'code' => '<textarea name="tx_scheduler[excludeExtensionsFromCheck]" id="task_excludeExtensionsFromCheck">' . $taskInfo['excludeExtensionsFromCheck'] . '</textarea>',
				'label' => $this->translate('task.checkExtensionsTask.fieldProvider.excludeExtensionsFromCheck.label')
			),

		);

		return $additionalFields;

	}


	/**
	 * Validates the input value(s)
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject
	 * @return bool
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

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
		$emailMailTos = \PeterBenke\PbCheckExtensions\Utility\StringUtility::explodeAndTrim(',', $submittedData['emailMailTo']);

		foreach($emailMailTos as $emailMailTo){
			if(empty($emailMailTo) || filter_var($emailMailTo, FILTER_VALIDATE_EMAIL) === FALSE) {
				$ok = false;
				$errorMessages[] = $this->translate('task.checkExtensionsTask.fieldProvider.emailMail.validation') . ': ' . $emailMailTo;
			}
		}

		if($ok){
			return true;
		}

		$parentObject->addMessage(implode(' / ', $errorMessages), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		return false;

	}


	/**
	 * Saves the input value
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {

		$task->emailSubject = trim($submittedData['emailSubject']);
		$task->emailMailFrom = trim($submittedData['emailMailFrom']);
		$task->emailMailTo = trim($submittedData['emailMailTo']);
		$task->excludeExtensionsFromCheck = trim($submittedData['excludeExtensionsFromCheck']);

	}


	/**
	 * @param string $key
	 * @return null|string
	 */
	protected function translate($key){

		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'pb_check_extensions');

	}

}
