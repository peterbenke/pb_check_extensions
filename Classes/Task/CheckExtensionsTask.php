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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class CheckExtensionsTask extends AbstractTask
{

    /**
     * @var string|null
     */
    protected ?string $emailSubject;

    /**
     * @var string|null
     */
    protected ?string $emailMailFrom;

    /**
     * @var string|null
     */
    protected ?string $emailMailTo;

    /**
     * @var string|null
     */
    protected ?string $excludeExtensionsFromCheck;

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(?string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
    }

    public function getEmailMailFrom(): ?string
    {
        return $this->emailMailFrom;
    }

    public function setEmailMailFrom(?string $emailMailFrom): void
    {
        $this->emailMailFrom = $emailMailFrom;
    }

    public function getEmailMailTo(): ?string
    {
        return $this->emailMailTo;
    }

    public function setEmailMailTo(?string $emailMailTo): void
    {
        $this->emailMailTo = $emailMailTo;
    }

    public function getExcludeExtensionsFromCheck(): ?string
    {
        return $this->excludeExtensionsFromCheck;
    }

    public function setExcludeExtensionsFromCheck(?string $excludeExtensionsFromCheck): void
    {
        $this->excludeExtensionsFromCheck = $excludeExtensionsFromCheck;
    }

    /**
     * Executes the scheduler job
     * @return bool
     * @author Peter Benke <info@typomotor.de>
     */
    public function execute(): bool
    {
        $this->checkExtensions();
        return true;
    }

    /**
     * Checks, if there are updates available for installed extensions
     * @return void
     * @author Peter Benke <info@typomotor.de>
     */
    protected function checkExtensions(): void
    {

        /**
         * @var ListUtility $listUtility
         * @var ExtensionRepository $extensionRepository
         * @var MailMessage $mailMessage
         */
        $listUtility = GeneralUtility::makeInstance(ListUtility::class);
        $extensionRepository = GeneralUtility::makeInstance(ExtensionRepository::class);
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);

        $extensions = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();
        $emailSuccessMessage = '';
        $emailErrorMessage = '';

        $excludeExtensions = PBStringUtility::explodeAndTrim(',', $this->excludeExtensionsFromCheck);

        // print_r($extensions);

        // Loop through the installed extensions
        foreach ($extensions as $extensionKey => $extensionData) {

            if (
                // No system extensions
                $extensionData['type'] != 'System'
                &&
                // Only installed extensions
                $extensionData['installed'] == '1'
                &&
                // Only extensions, which are not excluded
                !in_array($extensionKey, $excludeExtensions)
            ) {

                /*
                echo '<pre>';
                print_r(['Extension key'=> $extensionKey]);
                print_r($extensionData);
                echo '</pre>';
                */

                // Note: version 'dev-master' will be also supported
                $extensionData['version'] = str_replace('v', '', $extensionData['version']);
                $versionInt = VersionNumberUtility::convertVersionNumberToInteger($extensionData['version']);
                $extensionObject = $extensionRepository->findHighestAvailableVersion($extensionKey);

                if ($extensionObject instanceof Extension) {

                    $highestAvailableVersion = $extensionObject->getVersion();
                    $highestAvailableVersionInt = VersionNumberUtility::convertVersionNumberToInteger($highestAvailableVersion);

                    if ($highestAvailableVersionInt > $versionInt) {
                        $emailSuccessMessage .= $extensionKey . ':' . PHP_EOL;
                        $emailSuccessMessage .= $this->translate('task.checkExtensionsTask.current') . ' ' . $extensionData['version'];
                        $emailSuccessMessage .= ' / ';
                        $emailSuccessMessage .= $this->translate('task.checkExtensionsTask.available') . ' ' . $highestAvailableVersion . PHP_EOL;
                        $emailSuccessMessage .= PHP_EOL;
                    }

                } else {
                    $emailErrorMessage .= '- ' . $extensionKey . PHP_EOL;
                }

                unset($versionInt, $extensionObject, $highestAvailableVersion, $highestAvailableVersionInt);

            }

        }

        $emailTos = PBStringUtility::explodeAndTrim(',', $this->emailMailTo);

        if (!empty($emailSuccessMessage)) {
            $emailSuccessMessageIntro = '';
            $emailSuccessMessageIntro .= $this->translate('task.checkExtensionsTask.email.success.intro.1');
            $emailSuccessMessageIntro .= PHP_EOL . PHP_EOL;
            $mailMessage
                ->setFrom($this->emailMailFrom)
                ->setTo($emailTos)
                ->setSubject($this->emailSubject)
                ->text($emailSuccessMessageIntro . $emailSuccessMessage)
                ->html($emailSuccessMessageIntro . "<hr><br>" . nl2br($emailSuccessMessage))
                ->send();
        }

        if (!empty($emailErrorMessage)) {
            $emailErrorMessageIntro = $this->translate('task.checkExtensionsTask.email.error.intro.1') . PHP_EOL;
            $emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.2') . PHP_EOL;
            $emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.email.error.intro.3') . PHP_EOL;
            $emailErrorMessageIntro .= PHP_EOL;
            $emailErrorMessageIntro .= $this->translate('task.checkExtensionsTask.extensions') . PHP_EOL;
            $emailErrorMessageIntro .= PHP_EOL;
            $mailMessage
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
        return LocalizationUtility::translate($key, 'PbCheckExtensions');
    }

}