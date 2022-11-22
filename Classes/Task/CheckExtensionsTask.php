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
use TYPO3\CMS\Extbase\Object\Exception as TYPO3CMSExtbaseObjectException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class CheckExtensionsTask
 */
class CheckExtensionsTask extends AbstractTask
{

	/**
	 * @var string|null
	 */
	public ?string $emailSubject;

	/**
	 * @var string|null
	 */
	public ?string $emailMailFrom;

	/**
	 * @var string|null
	 */
	public ?string $emailMailTo;

	/**
	 * @var string|null
	 */
	public ?string $excludeExtensionsFromCheck;

	/**
	 * Executes the scheduler job
	 * @return bool
	 * @throws TYPO3CMSExtbaseObjectException
	 */
	public function execute(): bool
	{

		$this->checkExtensions();
		return true;

	}

	/**
	 * Checks, if there are updates available for installed extensions
	 * @throws TYPO3CMSExtbaseObjectException
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
				->text($emailSuccessMessageIntro . $emailSuccessMessage)
				->html($emailSuccessMessageIntro ."<hr><br>" . nl2br( $emailSuccessMessage))
				->send();
		}

		if(!empty($emailErrorMessage)){
			$emailErrorMessageIntro = $this->translate('task.checkExtensionsTask.email.error.intro.1') . PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.2')  . PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.3')  . PHP_EOL;
			$emailErrorMessageIntro .= PHP_EOL;
			$emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.extensions')  . PHP_EOL;
			$emailErrorMessageIntro .= PHP_EOL;
			$email
				->setFrom($this->emailMailFrom)
				->setTo($emailTos)
				->setSubject($this->emailSubject . ' - ' . $this->translate('task.checkExtensionsTask.error'))
				->text($emailErrorMessageIntro . $emailErrorMessage)
				->html($emailErrorMessageIntro . "<hr><br>" . nl2br($emailErrorMessage))
				->send();
		}

	}

	/**
	 * @param string $key
	 * @return string|null
	 * @author Peter Benke <info@typomotor.de>
	 */
	protected function translate(string $key): ?string
	{

		return LocalizationUtility::translate($key, 'pb_check_extensions');

	}

}