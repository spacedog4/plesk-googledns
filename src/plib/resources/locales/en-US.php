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

$messages = [
    'actionsColumn'                      => 'Actions',
    'buttonNo'                           => 'No',
    'buttonYes'                          => 'Yes',
    'pageTitle'                          => 'Google DNS',
    'indexPageTitle'                     => 'Google DNS Authentication',
    'clientIdLabel'                      => 'Client ID',
    'clientIdHint'                       => 'Go to API Console, click on edit or create a OAuth 2.0',
    'clientSecretLabel'                  => 'Client Secret',
    'clientSecretHint'                   => 'Go to API Console, click on edit or create a OAuth 2.0',
    'projectIdLabel'                     => 'Project ID',
    'projectIdHint'                      => 'Go to API Console, click to select a project, the Id is on the second column',
    'googlednsCredentials'               => 'Google DNS API Credentials',
    'googlednsDescriptionWindow'         => 'Enter your Google DNS API Credentials to get started. You can generate these in your Google API Console.',
    'authDataSaved'                      => 'The credentials have been saved',
    'domains'                            => 'Domains',
    'domainList'                         => 'Domain List',
    'domainsColumn'                      => 'Domain',
    'enabledColumn'                      => 'Enabled',
    'on'                                 => 'On',
    'off'                                => 'Off',
    'enableDomainButton'                 => 'Enable',
    'enableDomainsButton'                => 'Enable auto sync',
    'enableDomainsHint'                  => 'Enable selected domains',
    'disableDomainButton'                => 'Disable',
    'disableDomainsButton'               => 'Disable auto sync',
    'disableDomainsHint'                 => 'Disable selected domains',
    'multipleDomainsEnabled'             => 'Domain #%s has been enabled.',
    'multipleDomainsDisabled'            => 'Domain #%s has been disabled.',
    'domainEnabled'                      => 'The selected domain has been enabled',
    'domainDisabled'                     => 'The selected domain has been disabled',
    'domainsEnabled'                     => 'The selected domains have been enabled',
    'domainsDisabled'                    => 'The selected domains have been disabled',
    'syncDomainsButton'                  => 'Sync domains manually',
    'syncDomainsHint'                    => 'Manually sync the selected domains',
    'autoSync'                           => 'Auto sync',
    'enableAutoSync'                     => 'Enable auto sync',
    'disableAutoSync'                    => 'Disable auto sync',
    'domainsProcessed'                   => 'The domains have been processed',
    'overrideTtlLabel'                   => 'Override TTL',
    'overrideTtlHint'                    => 'Please enter a value in seconds. Leave empty to use Plesk\'s default.',
    'syncNewDomainsLabel'                => 'Sync new domains automatically',
    'proxyByDefaultLabel'                => 'Proxy through Google DNS by default',
    'googleDnsStatusEnabledMessage'      => 'Authentication provided',
    'googleDnsStatusDisabledMessage'     => 'No authentication provided',
    'googleDnsStatusDisabledMessageHint' => 'Click on the button above to connect to your Google Cloud Account',
    'googlednsConnect'                   => 'Login with Google',
    'googlednsConnectHint'               => 'Allow this app to access your Google Cloud DNS API',
    'googlednsDisconnect'                => 'Logout from Google',
    'googlednsDisconnectHint'            => 'Revoke access from this app to you Google Cloud DNS API',
    'googlednsMissing'                   => 'This domain is not in Google DNS',
    'formSendTitle'                      => 'Save infos',

    'helpToGetInfo'  => 'Where can I get those informations?',
    'step1Title'     => 'You need o active Cloud DNS API on Google API Console',
    'hintGoTo'       => 'Go to',
    'hintProject'    => 'Choose your project',
    'hintLeftMenu'   => 'On the left menu, go to',
    'step1Hint3'     => 'Search for "DNS" and select',
    'step1Hint4'     => 'Active "Cloud DNS API", you may need to enable billing',
    'step2Title'     => 'If you are using a domain in plesk instead of the IP Address, you must add it to the',
    'step2TitleLink' => 'authorized domains list',
    'step2Hint3Link' => 'OAuth consent screen',
    'step2Hint4'     => 'Choose External',
    'step2Hint5'     => 'Scroll to "Authorized domains" and add your domain without any http/https or path. Ex.: my-plesk-domain.com',
    'step2Hint6'     => 'Press Enter',
    'step2Hint7'     => 'You must fill "Application name"',
    'step2Hint8'     => 'Save it',
    'step3Title'     => 'You have to create a OAuth 2.0 credential on Google API Console',
    'step3Hint3Link' => 'Credentials',
    'step3Hint4'     => 'Click on "Create Credentials"',
    'step3Hint5'     => 'Click on "OAuth client ID"',
    'step3Hint6'     => 'Choose "Web Application"',
    'step3Hint7'     => 'Inside "Authorized redirect URIs" add your plesk domain/ip followed by */modules/googledns/index.php/index/authenticate*. Ex.: https://my-plesk-domain.com/modules/googledns/index.php/index/authenticate',
    'step3Hint8'     => 'Save "Your Client ID" and "Your Client Secret" to set it on plesk extension later',
    'supportMe'      => "To support me maintain this extension, you can make me a donation of any value"
];
