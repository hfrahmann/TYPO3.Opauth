<?php
namespace TYPO3\Opauth\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Opauth".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Class OpauthResponse
 * @Flow\Scope("singleton")
 */
class OpauthAccountService {

    /**
     * @var \TYPO3\Flow\Security\AccountFactory
     * @Flow\Inject
     */
    protected $accountFactory;

    /**
     * @var \TYPO3\Flow\Security\AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @var \TYPO3\Opauth\Opauth\Configuration
     * @Flow\Inject
     */
    protected $configuration;

    /**
     * @param \TYPO3\Opauth\Opauth\OpauthResponse $opauthResponse
     * @return string
     * @throws \TYPO3\Opauth\Exception
     */
    public function createAccountIdentifier(\TYPO3\Opauth\Opauth\OpauthResponse $opauthResponse) {
        if($opauthResponse == NULL)
            throw new \TYPO3\Opauth\Exception("OpauthResponse cannot be NULL.", 1381596920);

        $strategy = $opauthResponse->getStrategy();
        $userID = $opauthResponse->getUserID();

        if(strlen($strategy) > 0 && strlen($userID) > 0) {
            return $strategy . ':' . $userID;
        } else {
            throw new \TYPO3\Opauth\Exception("No Strategy or UserID given.", 1381596915);
        }
    }

    /**
     * @param \TYPO3\Opauth\Opauth\OpauthResponse $opauthResponse
     * @return \TYPO3\Flow\Security\Account
     * @throws \TYPO3\Opauth\Exception
     */
    public function getAccount(\TYPO3\Opauth\Opauth\OpauthResponse $opauthResponse) {
        if($opauthResponse == NULL)
            throw new \TYPO3\Opauth\Exception("OpauthResponse cannot be NULL.", 1381596921);

        $accountIdentifier = $this->createAccountIdentifier($opauthResponse);
        $roleIdentifier = $this->configuration->getDefaultRoleIdentifier();
        $authenticationProviderName = $this->configuration->getAuthenticationProviderName();

        $roleIdentifierArray = array();
        if(is_array($roleIdentifier))
            $roleIdentifierArray = $roleIdentifier;
        if(is_string($roleIdentifier))
            $roleIdentifierArray = array($roleIdentifier);

        $account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, $authenticationProviderName);
        if($account === NULL)
            $account = $this->accountFactory->createAccountWithPassword($accountIdentifier, NULL, $roleIdentifierArray, $authenticationProviderName);

        return $account;
    }

    /**
     * @param \TYPO3\Flow\Security\Account $account
     * @return bool
     */
    public function isAccountExisting(\TYPO3\Flow\Security\Account $account) {
        $accountIdentifier = $account->getAccountIdentifier();
        $authenticationProviderName = $account->getAuthenticationProviderName();

        $existingAccount = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, $authenticationProviderName);
        return ($existingAccount !== NULL);
    }

}

?>