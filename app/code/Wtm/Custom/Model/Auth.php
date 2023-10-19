<?php
namespace Wtm\Custom\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Phrase;

class Auth extends \Magento\Backend\Model\Auth
{
    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credentialStorage;

    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Backend\Helper\Data $backendData, \Magento\Backend\Model\Auth\StorageInterface $authStorage, \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage, \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig, \Magento\Framework\Data\Collection\ModelFactory $modelFactory)
    {
        parent::__construct($eventManager, $backendData, $authStorage, $credentialStorage, $coreConfig, $modelFactory);
        $this->_credentialStorage = $credentialStorage;
    }

    /**
     * Return credential storage object
     *
     * @return null|\Magento\Backend\Model\Auth\Credential\StorageInterface
     * @codeCoverageIgnore
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(__('Invalid login or password.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                $this->_eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );
            }

            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(__('Invalid login or password.'));
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage()? : 'Invalid login or password.')
            );
        }
    }
    /**
     * Throws specific Backend Authentication \Exception
     *
     * @param \Magento\Framework\Phrase $msg
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @static
     */
    public static function throwException(Phrase $msg = null)
    {
        if ($msg === null) {
            $msg = __('Authentication error occurred.');
        }
        throw new AuthenticationException($msg);
    }
}
