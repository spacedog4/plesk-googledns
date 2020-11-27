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

use PleskExt\GoogleDns\Db;

/**
 * Class Module_Googledns_Client
 */
class Modules_Googledns_Client {

    const AUTH_URI = "https://accounts.google.com/o/oauth2/auth";
    const TOKEN_URI = "https://oauth2.googleapis.com/token";
    const AUTH_PROVIDER_X506_CERT_URL = "https://www.googleapis.com/oauth2/v1/certs";

    const SERVICE_VERSION = '1.6.9.1';

    private $client_id = '';
    private $client_secret = '';
    private $project_id = '';

    private $redirect_uris = [];

    private $google_client;

    public static $zones = [];

    public $logger;

    /**
     * Singleton
     *
     * Modules_Googledns_Client constructor.
     *
     * @param $client_id
     * @param $client_secret
     * @param $project_id
     * @throws Google_Exception
     */
    protected function __construct($client_id, $client_secret, $project_id)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->project_id = $project_id;

        $this->logger = pm_Bootstrap::getContainer()->get(Psr\Log\LoggerInterface::class);

        pm_Log::info()

        $this->redirect_uris = [
            ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https://" : "http://") .
            $_SERVER['HTTP_HOST'] .
            pm_Context::getActionUrl('index', 'authenticate')
        ];

        $this->createGoogleClient();
    }

    /**
     * Get an instance of the Cloudflare Client
     *
     * @param string $client_id
     * @param string $client_secret
     * @param string $project_id
     * @return static|null
     * @throws Google_Exception
     */
    public static function getInstance($client_id = '', $client_secret = '', $project_id = '')
    {
        static $instance = null;
        if ($instance === null || $client_id) {
            if (!$client_id) {
                $client_id = pm_Settings::get(Modules_Googledns_Form_Settings::CLIENT_ID);
            }
            if (!$client_secret) {
                $client_secret = pm_Settings::get(Modules_Googledns_Form_Settings::CLIENT_SECRET);
            }
            if (!$project_id) {
                $project_id = pm_Settings::get(Modules_Googledns_Form_Settings::PROJECT_ID);
            }
            $instance = new static($client_id, $client_secret, $project_id);
        }

        return $instance;
    }

    /**
     * Get all domain names for the Google DNS account
     *
     * @return string[]
     * @throws Exception
     */
    public function getDomainNames()
    {
        if (static::$zones) {
            return array_values(static::$zones);
        }

        $client = $this->authenticate();
        $dns = new Google_Service_Dns($client);

        $results = $dns->managedZones->listManagedZones(pm_Settings::get(Modules_Googledns_Form_Settings::PROJECT_ID));

        if (!isset($results['managedZones'])) {
            $msg = 'Google DNS Error: ' . json_encode($results);
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        static::$zones = array_combine(
            array_column($results['managedZones'], 'name'),
            array_map(function ($result) {
                return rtrim($result['dnsName'], '.');
            }, $results['managedZones'])
        );

        return array_map(function ($result) {
            return rtrim($result['dnsName'], '.');
        }, $results['managedZones']);
    }

    /**
     * @param $domains
     *
     * @throws Exception
     * @throws Google_Exception
     * @throws Zend_Db_Adapter_Exception
     * @throws pm_Exception
     */
    public function syncDomains($domains)
    {
        $googlednsDomains = $this->getDomainNames();

        $domains = array_intersect($domains, $googlednsDomains);

        foreach ($domains as $domain) {
            // Detect updated records
            $pleskDomain = pm_Domain::getByName($domain);
            $pleskRecords = static::getPleskDnsEntries($domain);

            $equalsRecordsByType = [];
            foreach ($pleskRecords as $pleskRecord) {
                if ($pleskRecord['type'] != 'MX') {
                    if (isset($equalsRecordsByType[$pleskRecord['type']]) && in_array($pleskRecord['name'], $equalsRecordsByType[$pleskRecord['type']])) {
                        $msg = "Google DNS Error: You have two or more records with the same name and type, that is not allowed!";
                        $this->logger->error($msg);
                        throw new pm_Exception($msg);
                    } else {
                        $equalsRecordsByType[$pleskRecord['type']][] = $pleskRecord['name'];
                    }
                }
            }

            $googlednsRecords = static::getGooglednsEntries($domain);

            $newRecords = [];
            foreach ($pleskRecords as $id => $pleskRecord) {
                $name = static::formatNameForGoogledns($domain, $pleskRecord['name']);
                $type = $pleskRecord['type'];

                $found = false;
                foreach ($googlednsRecords as $googlednsRecord) {
                    if ($googlednsRecord['name'] === $name && $googlednsRecord['type'] === $type) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $newRecords[] = $pleskRecord;
                }
            }

            // Detect updated domains and change them in Google DNS
            $updatedRecords = [];
            foreach (static::getSavedDnsEntries($pleskDomain->getName()) as $savedId => $savedRecord) {
                foreach ($pleskRecords as $pleskRecord) {
                    if ($pleskRecord['name'] === $savedRecord['name']
                        && $pleskRecord['type'] === $savedRecord['type']
                        && $pleskRecord['content'] !== $savedRecord['content']
                    ) {
                        $found = false;
                        foreach ($googlednsRecords as $googlednsRecord) {
                            if ($googlednsRecord['name'] === $pleskRecord['name']
                                && $googlednsRecord['type'] === $pleskRecord['type']
                            ) {
                                $found = true;
                                break;
                            }
                        }
                        if ($found) {
                            $updatedRecords[] = $pleskRecord;
                        }
                        continue 2;
                    } elseif ($pleskRecord['name'] === $savedRecord['name']
                        && $pleskRecord['type'] === $savedRecord['type']
                        && $pleskRecord['content'] === $savedRecord['content']
                    ) {
                        continue 2;
                    }
                }
            }

            // Detect removed domains and remove them from Google DNS as well
            $pleskRecordIds = array_keys($pleskRecords);
            $removedRecords = [];
            foreach (static::getSavedDnsEntries($pleskDomain->getName()) as $savedId => $savedRecord) {
                if (!in_array($savedId, $pleskRecordIds)) {
                    foreach ($googlednsRecords as $googlednsRecord) {
                        if ($savedRecord['type'] === 'NS') {
                            continue;
                        }

                        if ($googlednsRecord['name'] === $savedRecord['name']
                            && $googlednsRecord['type'] === $savedRecord['type']
                        ) {
                            $removedRecords[] = $savedRecord;
                            break;
                        }
                    }
                }
            }

            $zones = array_flip($this->getZones());
            $dns = new Google_Service_Dns($this->google_client);
            $additions = [];
            $deletions = [];

            // Upload new to Google DNS
            foreach ($newRecords as $newRecord) {
                $additions[] = Modules_Googledns_Form_Settings::createResourceRecordSet($pleskDomain, $newRecord);
            }

            // Upload updated to Google DNS
            foreach ($updatedRecords as $updatedRecord) {
                $googlednsFound = null;
                foreach ($googlednsRecords as $googlednsRecord) {
                    if ($googlednsRecord['name'] === $updatedRecord['name']
                        && $googlednsRecord['type'] === $updatedRecord["type"]
                    ) {
                        $googlednsFound = $googlednsRecord;
                        break;
                    }
                }

                if (!$googlednsFound) {
                    continue;
                }

                $deletions[] = Modules_Googledns_Form_Settings::createResourceRecordSet($pleskDomain, $googlednsFound, true);
                $additions[] = Modules_Googledns_Form_Settings::createResourceRecordSet($pleskDomain, $updatedRecord);
            }

            // Remove from Google DNS
            foreach ($removedRecords as $removedRecord) {
                $googlednsFound = null;

                foreach ($googlednsRecords as $googlednsRecord) {
                    if ($googlednsRecord['name'] === $removedRecord['name']
                        && $googlednsRecord['type'] === $removedRecord["type"]
                    ) {
                        $googlednsFound = $googlednsRecord;
                        break;
                    }
                }

                if (!$googlednsFound) {
                    continue;
                }

                $deletions[] = Modules_Googledns_Form_Settings::createResourceRecordSet($pleskDomain, $googlednsFound, true);
            }

            $change = new Google_Service_Dns_Change();
            $change->setAdditions($additions);
            $change->setDeletions($deletions);
            try {
                $dns->changes->create(pm_Settings::get(Modules_Googledns_Form_Settings::PROJECT_ID), $zones[$domain], $change);
            } catch (Exception $e) {
                $msg = "Google DNS Error: " . $e->getMessage();
                $this->logger->error($msg);
                throw new pm_Exception($msg);
            }

            /** @var array $entry */
            $this->setDomainInfo($pleskDomain->getName(), array_map(function ($entry) {
                return [
                    'name'    => $entry['name'],
                    'type'    => $entry['type'],
                    'content' => $entry['content']
                ];
            }, $pleskRecords));
        }
    }

    /**
     * Return saved DNS entries
     *
     * @param string $id ID = Domain name
     *
     * @return array
     *
     */
    public static function getSavedDnsEntries($id)
    {
        return static::getDnsEntries($id, false);
    }

    /**
     * Return Plesk DNS entries
     *
     * @param string $id ID = Domain name
     *
     * @return array
     *
     */
    public static function getPleskDnsEntries($id)
    {
        return static::getDnsEntries($id, true);
    }

    /**
     * @param string $id ID = Domain name
     *
     * @return array
     *
     * @throws Exception
     * @throws Google_Exception
     */
    public static function getGooglednsEntries($id)
    {
        return static::getInstance()->googlednsEntries($id);
    }

    /**
     * @param string $domainName ID = Domain name
     *
     * @return array
     *
     * @throws Exception
     */
    public function googlednsEntries($domainName)
    {
        $zoneId = array_flip($this->getZones())[$domainName];
        if (!$zoneId) {
            $msg = 'Google DNS domain not found';
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        $client = $this->authenticate();
        $dns = new Google_Service_Dns($client);

        try {
            $result = $dns->resourceRecordSets->listResourceRecordSets(pm_Settings::get(Modules_Googledns_Form_Settings::PROJECT_ID), $zoneId);
        } catch (Exception $e) {
            $msg = 'Google DNS domain not found';
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        if (!isset($result['rrsets'])) {
            $msg = 'Google DNS Error: Rrsets parameter not found';
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        $records = [];
        foreach ($result['rrsets'] as $dnsEntry) {
            /** @var array $dnsEntry */
            $name = rtrim($dnsEntry['name'], '.');
            $content = str_replace('"', '', $dnsEntry['rrdatas']);
            $records["{$name}||{$dnsEntry['type']}"] = [
                'name'         => $dnsEntry['name'],
                'ttl'          => $dnsEntry['ttl'],
                'type'         => $dnsEntry['type'],
                'content'      => $content,
                'googledns_id' => $dnsEntry['name'],
            ];
        }

        return $records;
    }


    private static function getDnsEntries($domainName, $refresh = false)
    {
        $pleskDomain = pm_Domain::getByName($domainName);
        if (!$refresh) {
            try {
                $query = Db::adapter()->prepare("SELECT * FROM googledns_domains WHERE domain = :domain");
                $query->execute([
                    'domain' => $domainName
                ]);
                $localRecords = $query->fetch();
                if (!$localRecords) {
                    $localRecords = ['dns' => ''];
                }
                $localRecords = isset($localRecords['dns']) ? $localRecords['dns'] : '';
                $localRecords = (array)@json_decode($localRecords, true);
            } catch (Exception $e) {
                $localRecords = [];
            }
            $records = [];
            foreach ($localRecords as $localRecord) {
                // Skip invalid entries
                if (!isset($localRecord['name']) || !isset($localRecord['type']) || !isset($localRecord['content'])) {
                    continue;
                }

                $record_key = "{$localRecord['name']}||{$localRecord['type']}";
                $records[$record_key] = [
                    'name'    => $localRecord['name'],
                    'ttl'     => Modules_Googledns_Form_Settings::getTtl($pleskDomain->getId()),
                    'type'    => $localRecord['type'],
                    'content' => $localRecord['content'],
                    'opt'     => $localRecord['opt'],
                ];
            }

            return $records;
        }

        $domain = $pleskDomain->getName();
        $request = <<<APICALL
<packet>
<dns>
 <get_rec>
  <filter>
   <site-id>{$pleskDomain->getId()}</site-id>
  </filter>
 </get_rec>
</dns>
</packet>
APICALL;
        $records = [];
        $response = pm_ApiRpc::getService(static::SERVICE_VERSION)->call($request);
        if (isset($response->dns->get_rec->result)) {
            foreach (json_decode(json_encode($response->dns->get_rec), true)['result'] as $localRecord) {
                $name = static::formatNameForGoogledns($domain, $localRecord['data']['host']);
                $type = $localRecord['data']['type'];
                $content = static::formatContentForGoogledns($domain, $localRecord['data']['value']);

                if ($type === 'NS') {
                    continue;
                }

                $record_key = "{$name}||{$type}";
                if (array_key_exists($record_key, $records)) {
                    $records[$record_key]['content'][] = $content;
                } else {
                    $records[$record_key] = [
                        'name'    => $name,
                        'ttl'     => Modules_Googledns_Form_Settings::getTtl($pleskDomain->getId()),
                        'type'    => $type,
                        'content' => [
                            $content,
                        ],
                        'opt'     => $localRecord['data']['opt']
                    ];
                }
            }
        }

        return $records;
    }

    /**
     * Save domain info, in order to track changes
     *
     * @param string $domainName Plesk ID of domain
     * @param array $info DNS Entries
     *
     * @throws Zend_Db_Adapter_Exception
     */
    private function setDomainInfo($domainName, $info)
    {
        $query = Db::adapter()->prepare("DELETE FROM googledns_domains WHERE domain = :domain");
        $query->execute([
            'domain' => $domainName
        ]);

        $query = Db::adapter()->prepare("INSERT INTO googledns_domains (domain, dns) VALUES(:domain, :dns)");
        $query->execute([
            'domain' => $domainName,
            'dns'    => json_encode($info)
        ]);
    }

    /**
     * @param string $domain
     * @param string $name
     *
     * @return string
     */
    public static function formatNameForGoogledns($domain, $name)
    {
        return $name;
    }

    /**
     * @param string $domain
     * @param string $content
     *
     * @return string
     */
    public static function formatContentForGoogledns($domain, $content)
    {
        return $content;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getZones()
    {
        if (!static::$zones) {
            $this->getDomainNames();
        }

        return static::$zones;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function authenticate()
    {
        if (!$access_token = json_decode(pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN), true)) {
            $msg = 'Google DNS Error: Access Token not setted';
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        try {
            $this->google_client->setAccessToken($access_token);

            if ($this->google_client->isAccessTokenExpired()) {
                if (!$refresh_token = pm_Settings::get(Modules_Googledns_Form_Settings::REFRESH_TOKEN)) {
                    $this->revokeAccessToken();

                    $msg = 'Google DNS Error: Couldn\'t find refresh token';
                    $this->logger->error($msg);
                    throw new pm_Exception($msg);
                }

                if (!$new_token = $this->google_client->refreshToken(json_decode($refresh_token, true))) {
                    $msg = 'Google DNS Error: Couldn\'t refresh access token - ' . is_string($refresh_token) ? $refresh_token : json_encode($refresh_token);
                    $this->logger->error($msg);
                    throw new pm_Exception($msg);
                }

                pm_Settings::set(Modules_Googledns_Form_Settings::ACCESS_TOKEN, json_encode($new_token));
            }

            return $this->google_client;
        } catch (Exception $e) {
            pm_Settings::set(Modules_Googledns_Form_Settings::ACCESS_TOKEN, null);
            $this->logger->error(json_encode($e));
            throw $e;
        }
    }

    /**
     * @param $code
     * @return mixed
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function fetchAccessTokenFromCode($code)
    {
        $this->google_client->authenticate($code);

        try {
            if (!$access_token = $this->google_client->getAccessToken()) {
                $msg = "Cant get Google DNS Access Token";
                $this->logger->error($msg);
                throw new pm_Exception($msg);

            }
        } catch (Exception $e) {
            $msg = "Google DNS Error: " . json_encode($e);
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        if (!array_key_exists("refresh_token", $access_token)) {
            try {
                $this->revokeAccessToken($access_token);
            } catch (Exception $e) {
                $msg = "Google DNS Error: " . json_encode($e);
                $this->logger->error($msg);
                throw new pm_Exception($msg);
            }
            $msg = "Cant get Google DNS Refresh Token";
            $this->logger->error($msg);
            throw new pm_Exception($msg);
        }

        try {
            pm_Settings::set(Modules_Googledns_Form_Settings::REFRESH_TOKEN, json_encode($access_token['refresh_token']));

            pm_Settings::set(Modules_Googledns_Form_Settings::ACCESS_TOKEN, json_encode($access_token));
        } catch (Exception $e) {
            $this->logger->error("Cant set Google DNS Refresh and Access token on pm_Settings");
        }

        return $access_token;
    }

    /**
     * @return mixed
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Db_Table_Row_Exception
     * @throws pm_Exception_InvalidArgumentException
     */
    public function revokeAccessToken()
    {
        $access_token = json_decode(pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN), true);

        pm_Settings::set(Modules_Googledns_Form_Settings::ACCESS_TOKEN, null);

        return $this->google_client->revokeToken($access_token);
    }

    public function getGoogleOAuth2URL()
    {
        return $this->google_client->createAuthUrl();
    }

    /**
     * @throws Google_Exception
     */
    public function createGoogleClient()
    {
        $client = new Google_Client();
        if ($this->client_id && $this->project_id && $this->client_secret) {
            $client->setAuthConfig([
                "web" => [
                    "client_id"     => $this->client_id,
                    "project_id"    => $this->project_id,
                    "client_secret" => $this->client_secret,
                    "redirect_uris" => $this->redirect_uris
                ]
            ]);
        }
        $client->setRedirectUri($this->redirect_uris[0]);
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->addScope(Google_Service_Dns::NDEV_CLOUDDNS_READWRITE);

        $this->google_client = $client;
    }

}