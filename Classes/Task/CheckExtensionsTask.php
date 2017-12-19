<?php
namespace PeterBenke\PbCheckExtensions\Task;

/**
 * Class CheckExtensionsTask
 * @package PeterBenke\PbCheckExtensions\Task
 * @author Peter Benke <info@typomotor.de>
 */
class CheckExtensionsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask{

	/**
	 * @var string
	 */
	public $emailSubject;

	/**
	 * @var string
	 */
	public $emailMailFrom;

	/**
	 * @var string
	 */
	public $emailMailTo;

	/**
	 * @var string
	 */
	public $excludeExtensionsFromCheck;

	/**
	 * Executs the scheduler job
	 * @return bool
	 */
	public function execute(){

		$this->checkExtensions();
		return true;

	}

	/**
	 * Checks, if there are updates available fo rinstalled extensions
	 */
	protected function checkExtensions(){

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility
		 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository $extensionRepository
		 * @var \TYPO3\CMS\Core\Mail\MailMessage $email
		 */

		// Create objects
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
		$listUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ListUtility::class);

		$extensionRepository = $objectManager->get(\TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository::class);
		$extensions = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();

		$email = $objectManager->get(\TYPO3\CMS\Core\Mail\MailMessage::class);
		$emailSuccessMessage = '';
		$emailErrorMessage = '';

		$excludeExtensions = \Allplan\AllplanTools\Utility\StringUtility::explodeAndTrim(',', $this->excludeExtensionsFromCheck);

		// Loop through the installed extensions
		foreach($extensions as $extensionKey => $extensionData){

			if(
				// No system extensions
				!preg_match('#^typo3/sysext#', $extensionData['siteRelPath'])
				&&
				// Only installed extensions
				$extensionData['installed'] == '1'
				&&
				// Only extensions, which are not excluded
				!in_array($extensionKey, $excludeExtensions)
			){
				/*
				echo $extensionKey . ':<br>';
				echo 'Title: ' . $extensionData['title'];
				echo '<br>';
				echo 'SiteRelPath: ' . $extensionData['siteRelPath'];
				echo '<br>';
				echo 'Version: ' . $extensionData['version'];
				echo '<br>';
				echo 'Installed: ' . $extensionData['installed'];
				echo '<br>';
				echo '<hr>';
				*/

				$versionInt = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($extensionData['version']);
				$extensionObject = $extensionRepository->findHighestAvailableVersion($extensionKey);

				if ($extensionObject instanceof \TYPO3\CMS\Extensionmanager\Domain\Model\Extension) {

					$highestAvailableVersion = $extensionObject->getVersion();
					$highestAvailableVersionInt = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($highestAvailableVersion);

					if($highestAvailableVersionInt > $versionInt){
						$emailSuccessMessage .= $extensionKey . ':' . PHP_EOL;
						$emailSuccessMessage .= $this->translate('task.checkExtensionsTask.current') . ' ' . $extensionData['version'];
						$emailSuccessMessage .= ' / ';
						$emailSuccessMessage .= $this->translate('task.checkExtensionsTask.available') . ' ' .$highestAvailableVersion . PHP_EOL;
						$emailSuccessMessage .= PHP_EOL;
					}

				}else{
					$emailErrorMessage .= '- ' . $extensionKey . PHP_EOL;
				}

				unset($versionInt, $extensionObject, $highestAvailableVersion, $highestAvailableVersionInt);

			}

		}

		$emailTos = \Allplan\AllplanTools\Utility\StringUtility::explodeAndTrim(',', $this->emailMailTo);

		if(!empty($emailSuccessMessage)){
			$emailSuccessMessageIntro = '';
			$emailSuccessMessageIntro.= $this->translate('task.checkExtensionsTask.email.success.intro.1');
			$emailSuccessMessageIntro .= PHP_EOL . PHP_EOL;
			$email
				->setFrom($this->emailMailFrom)
				->setTo($emailTos)
				->setSubject($this->emailSubject)
				->setBody($emailSuccessMessageIntro . $emailSuccessMessage)
				->send();
		}

		if(!empty($emailErrorMessage)){
			$emailErrorMessageIntro = '';
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.1') . PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.2')  . PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.3')  . PHP_EOL;
			$emailErrorMessageIntro .= PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.extensions')  . PHP_EOL;
			$emailErrorMessageIntro .= PHP_EOL;
			$email
				->setFrom($this->emailMailFrom)
				->setTo($emailTos)
				->setSubject($this->emailSubject . ' - ' . $this->translate('task.checkExtensionsTask.error'))
				->setBody($emailErrorMessageIntro . $emailErrorMessage)
				->send();
		}

	}

	/**
	 * @param string $key
	 * @return null|string
	 */
	protected function translate($key){

		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'pb_check_extensions');

	}

}