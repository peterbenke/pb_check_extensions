<?php
namespace PeterBenke\PbCheckExtensions\Task;

/**
 * PbCheckExtensions
 */
use PeterBenke\PbCheckExtensions\Utility\StringUtility as PBStringUtility;

/**
 * TYPO3
 */
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class CheckExtensionsTask
 * @package PeterBenke\PbCheckExtensions\Task
 * @author Peter Benke <info@typomotor.de>
 */
class CheckExtensionsTask extends AbstractTask
{

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
	 * Executes the scheduler job
	 * @return bool
	 */
	public function execute(){

		$this->checkExtensions();
		return true;

	}

	/**
	 * Checks, if there are updates available for installed extensions
	 * @author Peter Benke <info@typomotor.de>
	 */
	protected function checkExtensions()
	{

		/**
		 * @var ObjectManager $objectManager
		 * @var ListUtility $listUtility
		 * @var ExtensionRepository $extensionRepository
		 * @var MailMessage $email
		 */

		// Create objects
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$listUtility = $objectManager->get(ListUtility::class);

		$extensionRepository = $objectManager->get(ExtensionRepository::class);
		$extensions = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();

		$email = $objectManager->get(MailMessage::class);
		$emailSuccessMessage = '';
		$emailErrorMessage = '';

		$excludeExtensions = PBStringUtility::explodeAndTrim(',', $this->excludeExtensionsFromCheck);

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

				$versionInt = VersionNumberUtility::convertVersionNumberToInteger($extensionData['version']);
				$extensionObject = $extensionRepository->findHighestAvailableVersion($extensionKey);

				if ($extensionObject instanceof Extension){

					$highestAvailableVersion = $extensionObject->getVersion();
					$highestAvailableVersionInt = VersionNumberUtility::convertVersionNumberToInteger($highestAvailableVersion);

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

		$emailTos = PBStringUtility::explodeAndTrim(',', $this->emailMailTo);

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
	 * @author Peter Benke <info@typomotor.de>
	 */
	protected function translate(string $key)
	{

		return LocalizationUtility::translate($key, 'pb_check_extensions');

	}

}