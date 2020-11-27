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
 * Class Modules_Googledns_EventListener
 */
class Modules_Googledns_EventListener implements EventListener {

    public function filterActions()
    {
        return [
            'domain_create',
            'domain_dns_update',
        ];
    }

    /**
     * @param $objectType
     * @param $objectId
     * @param $action
     * @param $oldValues
     * @param $newValues
     *
     */
    public function handleEvent($objectType, $objectId, $action, $oldValues, $newValues)
    {
        try {
            switch ($action) {
                case 'domain_create':
                    if (!pm_Settings::get(Modules_Googledns_Form_Settings::NEW_DOMAINS)) {
                        return;
                    }

                    $googlednsDomain = new pm_Domain($objectId);
                    $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
                    if (!is_array($savedDomains)) {
                        $savedDomains = [];
                    }
                    $savedDomains[] = $googlednsDomain->getName();
                    pm_Settings::set(Modules_Googledns_List_Domains::DOMAINS, json_encode($savedDomains));
                    break;
                case 'domain_dns_update':
                    // Push all new/updated entries of this domain
                    if (!pm_Settings::get(Modules_Googledns_Form_Settings::ACCESS_TOKEN)) {
                        return;
                    }

                    $domain = new pm_Domain($objectId);
                    $savedDomains = @json_decode(pm_Settings::get(Modules_Googledns_List_Domains::DOMAINS), true);
                    if (!is_array($savedDomains)) {
                        $savedDomains = [];
                    }
                    if (!in_array($domain->getName(), $savedDomains)) {
                        return;
                    }
                    Modules_Googledns_Client::getInstance()->syncDomains([$domain->getName()]);
                    break;
            }
        } catch (Exception $e) {
            $logger = pm_Bootstrap::getContainer()->get(Psr\Log\LoggerInterface::class);
            $logger->error(json_encode($e));
        }
    }
}

return new Modules_Googledns_EventListener();