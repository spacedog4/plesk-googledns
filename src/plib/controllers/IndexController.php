<?php
/**
 * Copyright 2020 André Luis Monteiro
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @copyright 2020 André Luis Monteiro
 * @author André Luis Monteiro <andre_luis_monteiro1998@hotmail.com>
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

class IndexController extends pm_Controller_Action {

    /**
     * @throws pm_Exception
     */
    public function init()
    {
        parent::init();

//        if (!pm_Session::getClient()->isAdmin()) {
//            throw new pm_Exception('Permission denied');
//        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->pageTitle = $this->lmsg('pageTitle');
        $this->view->tabs = $this->_getTabs();

    }

    /**
     * @throws Google_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     */
    public function indexAction()
    {
//        pm_Log::info("indexController Google DNS");
//        $logger = pm_Bootstrap::getContainer()->get(Psr\Log\LoggerInterface::class);
//        $logger->error('indexController Google DNS');

        $form = new Modules_Googledns_Form_Settings();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            try {
                $form->process();
                $this->_status->addInfo($this->lmsg('authDataSaved'));

            } catch (pm_Exception $e) {
                $this->_status->addError($e->getMessage());
            }
            $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
        }

        $this->view->form = $form;

        if (!pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN)) {
            $smallTools = [
                [
                    'title'       => $this->lmsg('googlednsConnect'),
                    'description' => $this->lmsg('googlednsConnectHint'),
                    'class'       => 'sb-activate',
                    'link'        => Modules_Googledns_Client::getInstance()->getGoogleOAuth2URL()
                ]
            ];
        } else {
            $smallTools = [
                [
                    'title'       => $this->lmsg('googlednsDisconnect'),
                    'description' => $this->lmsg('googlednsDisconnectHint'),
                    'class'       => 'sb-disable',
                    'controller'  => 'index',
                    'action'      => 'revoke',
                ]
            ];
        }

        $this->view->smallTools = $smallTools;

        $this->view->googleDnsStatusMessage = $this->lmsg('googleDnsStatusMessage');
    }

    /**
     * @throws Google_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function authenticateAction()
    {
        $code = $this->getRequest()->getParam('code');

        if ($code) {
            Modules_Googledns_Client::getInstance()->fetchAccessTokenFromCode($code);
        }

        $this->redirect('/');
    }

    /**
     * @throws Google_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function revokeAction()
    {
        Modules_Googledns_Client::getInstance()->revokeAccessToken();

        $this->redirect('/');
    }

    public function domainsAction()
    {
        if (pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN)) {
            try {
                $this->view->domainList = new Modules_Googledns_List_Domains($this->view, $this->getRequest(), $this->_helper);
            } catch (Exception $e) {
                $this->_status->addError($e->getMessage());
            }
        } else {
            $this->redirect('/');
        }
    }

    private function _getTabs()
    {
        $tabs = [];
        $tabs[] = [
            'title'  => $this->lmsg('indexPageTitle'),
            'action' => 'index',
        ];
        if (pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN)) {
            $tabs[] = [
                'title'  => $this->lmsg('domains'),
                'action' => 'domains',
            ];
        }
        return $tabs;
    }

    /**
     * Enable multiple domains
     *
     * @throws Exception
     * @throws Google_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function enableDomainsAction()
    {
        $messages = [];
        $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
        $googlednsDomains = Modules_Googledns_Client::getInstance()->getDomainNames();
        if (!is_array($savedDomains)) {
            $savedDomains = [];
        }
        foreach ((array)$this->_getParam('ids') as $id) {
            if (!in_array($id, $googlednsDomains)) {
                continue;
            }
            $savedDomains[] = $id;
            $messages[] = ['status' => 'info', 'content' => sprintf($this->lmsg('multipleDomainsEnabled'), $id)];
        }
        $savedDomains = self::domainCleanup($savedDomains);
        pm_settings::set(Modules_Googledns_List_Domains::DOMAINS, json_encode($savedDomains));
        $this->_helper->json(['status' => 'success', 'statusMessages' => $messages]);
    }

    /**
     * Disable multiple domains
     *
     * @throws Exception
     * @throws Google_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function disableDomainsAction()
    {
        $messages = [];
        $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
        $googlednsDomains = Modules_Googledns_Client::getInstance()->getDomainNames();
        if (!is_array($savedDomains)) {
            $savedDomains = [];
        }
        foreach ((array)$this->_getParam('ids') as $id) {
            if (!in_array($id, $googlednsDomains)) {
                continue;
            }
            $savedDomains = array_filter($savedDomains, function ($domain) use ($id) {
                return $domain !== $id;
            });
            $messages[] = ['status' => 'info', 'content' => sprintf($this->lmsg('multipleDomainsDisabled'), $id)];
        }
        $savedDomains = self::domainCleanup($savedDomains);
        pm_settings::set(Modules_Googledns_List_Domains::DOMAINS, json_encode($savedDomains));
        $this->_helper->json(['status' => 'success', 'statusMessages' => $messages]);
    }

    /**
     * Enable a single domain
     *
     * @throws Exception
     */
    public function enableDomainAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new pm_Exception('Permission denied');
        }

        $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
        if (!is_array($savedDomains)) {
            $savedDomains = [];
        }

        $savedDomains[] = $this->_getParam('id');
        $savedDomains = self::domainCleanup($savedDomains);

        try {
            pm_Settings::set(Modules_Googledns_List_Domains::DOMAINS, json_encode($savedDomains));
            $this->_status->addMessage('info', $this->lmsg('domainEnabled'));
        } catch (Exception $e) {
            $this->_status->addMessage('error', $e->getMessage());
        }
        $this->_redirect('index/domains');
    }

    /**
     * Disable a single domain
     *
     * @throws Exception
     */
    public function disableDomainAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new pm_Exception('Permission denied');
        }

        $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
        if (!is_array($savedDomains)) {
            $savedDomains = [];
        }
        $savedDomains = array_filter($savedDomains, function ($domain) {
            return $domain !== $this->_getParam('id');
        });
        $savedDomains = self::domainCleanup($savedDomains);
        try {
            pm_Settings::set(Modules_Googledns_List_Domains::DOMAINS, json_encode($savedDomains));
            $this->_status->addMessage('info', $this->lmsg('domainDisabled'));
        } catch (Exception $e) {
            $this->_status->addMessage('error', $e->getMessage());
        }
        $this->_redirect('index/domains');
    }

    /**
     * Get the domains via ajax
     *
     * @throws Exception
     */
    public function domainsDataAction()
    {
        $list = new Modules_Googledns_List_Domains($this->view, $this->getRequest(), $this->_helper);
        $this->_helper->json($list->fetchData());
    }

    /**
     * Sync the selected domains
     */
    public function syncDomainsAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new pm_Exception('Permission denied');
        }

        Modules_Googledns_Client::getInstance()->syncDomains((array)$this->_getParam('ids'));
        $this->_helper->json(['status' => 'success', 'statusMessages' => [['status' => 'info', 'content' => $this->lmsg('domainsProcessed')]]]);
    }

    /**
     * Clean the domain list
     *
     * @param array $savedDomains
     *
     * @return array
     * @throws Exception
     * @throws Google_Exception
     */
    private static function domainCleanup($savedDomains)
    {
        try {
            $googlednsDomains = Modules_Googledns_Client::getInstance()->getDomainNames();
        } catch (Exception $e) {
            throw $e;
        }

        return array_unique(array_filter($savedDomains, function ($domain) use ($googlednsDomains) {
            return in_array($domain, $googlednsDomains);
        }));
    }
}
