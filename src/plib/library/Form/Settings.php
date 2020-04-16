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

/**
 * Class Modules_Cloudflaredns_Form_Settings
 */
class Modules_Googledns_Form_Settings extends pm_Form_Simple
{
    const ACCESS_TOKEN = 'googledns_access_token';
    const REFRESH_TOKEN = 'googledns_refresh_token';
    const CLIENT_ID = 'googledns_client_id';
    const CLIENT_SECRET = 'googledns_client_secret';
    const PROJECT_ID = 'googledns_project_id';
    const OVERRIDE_TTL = 'googledns_override_ttl';
    const NEW_DOMAINS = 'googledns_new_domains';

    private $isConsole = false;

    /**
     * Modules_Googledns_Form_Settings constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!empty($options['isConsole'])) {
            $this->isConsole = $options['isConsole'];
        }

        parent::__construct($options);
    }

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->addElement('text', static::CLIENT_ID, [
            'label'       => pm_Locale::lmsg('clientIdLabel'),
            'value'       => pm_Settings::get(static::CLIENT_ID),
            'class'       => 'f-large-size',
            'required'    => true,
            'placeholder' => '',
            'validators'  => [
                ['NotEmpty', true],
            ],
        ]);
        $this->addElement('password', static::CLIENT_SECRET, [
            'label'       => pm_Locale::lmsg('clientSecretLabel'),
            'value'       => pm_Settings::get(static::CLIENT_SECRET),
            'required'    => false,
            'validators'  => [],
        ]);
        $this->addElement('text', static::PROJECT_ID, [
            'label'       => pm_Locale::lmsg('projectIdLabel'),
            'value'       => pm_Settings::get(static::PROJECT_ID),
            'class'       => 'f-large-size',
            'required'    => true,
            'validators'  => [
                ['NotEmpty', true],
            ],
        ]);
        $this->addElement('text', static::OVERRIDE_TTL, [
            'label'       => pm_Locale::lmsg('overrideTtlLabel'),
            'description' => pm_Locale::lmsg('overrideTtlHint'),
            'value'       => pm_Settings::get(static::OVERRIDE_TTL),
            'required'    => false,
            'validators'  => [],
        ]);
        $this->addElement('checkbox', static::NEW_DOMAINS, [
            'label'      => pm_Locale::lmsg('syncNewDomainsLabel'),
            'value'      => pm_Settings::get(static::NEW_DOMAINS),
            'required'   => false,
            'validators' => [],
        ]);
        $this->addControlButtons([
            'cancelLink' => pm_Context::getModulesListUrl(),
        ]);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function isValid($data)
    {
        if (!parent::isValid($data)) {
            $this->markAsError();
            $this->getElement(static::CLIENT_ID)->addError(pm_Locale::lmsg('clientIdClientSecretInvalidError'));
            $this->getElement(static::CLIENT_SECRET)->addError(pm_Locale::lmsg('clientIdClientSecretInvalidError'));

            return false;
        }

        return true;
    }

    /**
     * @return array
     *
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function process()
    {
        $res = [];

        $client_id = $this->getValue(static::CLIENT_ID);
        $client_secret = $this->getValue(static::CLIENT_SECRET);

        pm_Settings::set(static::OVERRIDE_TTL, $this->getValue(static::OVERRIDE_TTL));
        pm_Settings::set(static::PROJECT_ID, $this->getValue(static::PROJECT_ID));
        pm_Settings::set(static::NEW_DOMAINS, $this->getValue(static::NEW_DOMAINS));

        $this->saveUserData($client_id, $client_secret);

        return $res;
    }

    /**
     * @param $client_id
     * @param $client_secret
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    private function saveUserData($client_id, $client_secret)
    {
        pm_Settings::set(static::CLIENT_ID, $client_id);
        if ($client_secret) {
            pm_Settings::set(static::CLIENT_SECRET, $client_secret);
        }

    }

    /**
     * Get override TTL
     *
     * string $id Site ID
     *
     * @param string $id Site ID
     *
     * @return int TTL
     *
     * @throws pm_Exception
     */
    public static function getTtl($id)
    {
        static $saved = [];
        if (isset($saved[$id])) {
            return $saved[$id];
        }

        $savedTtl = pm_Settings::get(static::OVERRIDE_TTL);
        if (!$savedTtl) {
            $request = <<<APICALL
<packet>
<dns>
 <get>
  <filter>
   <site-id>{$id}</site-id>
  </filter>
  <soa/>
 </get>
</dns>
</packet>
APICALL;
            $response = pm_ApiRpc::getService(Modules_Googledns_Client::SERVICE_VERSION)->call($request);
            $savedTtl = isset($response->dns->get->result->soa->ttl) ? (int) $response->dns->get->result->soa->ttl : 300;
        }

        $saved[$id] = $savedTtl;

        return $savedTtl;
    }

    /**
     * @param $pleskDomain
     * @param $record
     * @return Google_Service_Dns_ResourceRecordSet
     * @throws pm_Exception
     */
    public static function createResourceRecordSet($pleskDomain, $record) {
        $addition = new Google_Service_Dns_ResourceRecordSet();
        $addition->setKind('dns#resourceRecordSet');
        $addition->setName($record['name']);

        $content = is_string($record['opt']) ? $record['opt'] . " " . $record['content'] : $record['content'];

        $addition->setRrdatas([
            $content
        ]);
        $addition->setTtl((int)static::getTtl($pleskDomain->getId()));
        $addition->setSignatureRrdatas([]);
        $addition->setType($record['type']);

        return $addition;
    }
}