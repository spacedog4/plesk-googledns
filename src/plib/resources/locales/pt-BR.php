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
    'actionsColumn'                      => 'Ações',
    'buttonNo'                           => 'Não',
    'buttonYes'                          => 'Sim',
    'pageTitle'                          => 'Google DNS',
    'indexPageTitle'                     => 'Google DNS Autenticação',
    'clientIdLabel'                      => 'Client ID',
    'clientIdHint'                       => 'Vá para o API Console, clique em editar ou criar um OAuth 2.0',
    'clientSecretLabel'                  => 'Client Secret',
    'clientSecretHint'                   => 'Vá para o API Console, clique em editar ou criar um OAuth 2.0',
    'projectIdLabel'                     => 'Project ID',
    'projectIdHint'                      => 'Vá para o API Console, clique para selecionar um projeto, o Id está na segunda coluna',
    'googlednsCredentials'               => 'Credenciais da API do Google DNS',
    'googlednsDescriptionWindow'         => 'Coloque suas credenciais da API do Google DNS para começar. Você pode encontrar elas no Google API Console.',
    'authDataSaved'                      => 'As credenciais foram salvas',
    'domains'                            => 'Domínios',
    'domainList'                         => 'Lista de Domínios',
    'domainsColumn'                      => 'Domínio',
    'enabledColumn'                      => 'Ativo',
    'on'                                 => 'Ativado',
    'off'                                => 'Desativado',
    'enableDomainButton'                 => 'Ativar',
    'enableDomainsButton'                => 'Ativar sincronização automática',
    'enableDomainsHint'                  => 'Ativar domínios selecionados',
    'disableDomainButton'                => 'Desativar',
    'disableDomainsButton'               => 'Desativar sincronização automática',
    'disableDomainsHint'                 => 'Desativar domínios selecionados',
    'multipleDomainsEnabled'             => 'Domínio #%s foi ativado.',
    'multipleDomainsDisabled'            => 'Domínio #%s foi desativado.',
    'domainEnabled'                      => 'O domínio selecionado foi ativado',
    'domainDisabled'                     => 'O domínio selecionado foi desativado',
    'domainsEnabled'                     => 'Os domínios selecionados foram ativados',
    'domainsDisabled'                    => 'Os domínios selecionados foram desativados',
    'syncDomainsButton'                  => 'Sincronizar domínios manualmente',
    'syncDomainsHint'                    => 'Sincroniza os domínios selecionados manualmente',
    'autoSync'                           => 'Sincronizar automaticamento',
    'enableAutoSync'                     => 'Ativar sincronização automática',
    'disableAutoSync'                    => 'Desativar sincronização automática',
    'domainsProcessed'                   => 'Os domínios foram processados',
    'overrideTtlLabel'                   => 'Sobreescrever TTL',
    'overrideTtlHint'                    => 'Por favor entre com o valor em segundos. Deixe em branco para usar o padrão do Plesk.',
    'syncNewDomainsLabel'                => 'Sincronizar novos domínios automaticamente',
    'googleDnsStatusEnabledMessage'      => 'Autenticado',
    'googleDnsStatusDisabledMessage'     => 'Não autenticado',
    'googleDnsStatusDisabledMessageHint' => 'Clique no botão acima para se conectar com sua conta do Google Cloud',
    'googlednsConnect'                   => 'Logar com Google',
    'googlednsConnectHint'               => 'Permitir acesso deste app a sua API do Google Cloud DNS',
    'googlednsDisconnect'                => 'Desconectar da Google',
    'googlednsDisconnectHint'            => 'Revogar o acesso deste app a sua API do Google Cloud DNS',
    'googlednsMissing'                   => 'Este domínio não está no Google DNS',
    'formSendTitle'                      => 'Salvar informações',

    'helpToGetInfo'  => 'Onde consigo essas informações?',
    'step1Title'     => 'Você precisa ativar o Cloud DNS API no Google API Console',
    'hintGoTo'       => 'Vá para',
    'hintProject'    => 'Escolhe o seu projeto',
    'hintLeftMenu'   => 'No menu a esquerda, vá para',
    'step1Hint3'     => 'Pesquise por "DNS" e selecione',
    'step1Hint4'     => 'Ative "Cloud DNS API", você talvez precisar-a ativar a cobrança em sua conta (Não se preocupe, nada será cobrado por que a cota é limitada)',
    'step2Title'     => 'Se você está usando um domínio no plesk ao invés de um endereço IP, você vai precisar adicionar ele a',
    'step2TitleLink' => 'lista de domínios autorizados',
    'step2Hint3Link' => 'Tela de consentimento OAuth',
    'step2Hint4'     => 'Escolha Externo',
    'step2Hint5'     => 'Role até "Domínios Autorizados" e adicione o domíno sem qualquer hhtp/https ou caminho. Ex.: my-plesk-domain.com',
    'step2Hint6'     => 'Pressione Enter',
    'step2Hint7'     => 'Você precisa preencher "Nome da Aplicação"',
    'step2Hint8'     => 'Salve',
    'step3Title'     => 'Vá deverá criar uma credencial para OAuth 2.0 no Google API Console',
    'step3Hint3Link' => 'Credenciais',
    'step3Hint4'     => 'Clique em "Criar crendiais"',
    'step3Hint5'     => 'Clique em "ID do cliente OAuth"',
    'step3Hint6'     => 'Escolhe "Aplicativo da Web"',
    'step3Hint7'     => 'Dentro de "URIs de redirecionamento autorizados" adicione seu domínio do plesk/endereço ip seguido por */modules/googledns/index.php/index/authenticate*. Ex.: https://my-plesk-domain.com/modules/googledns/index.php/index/authenticate',
    'step3Hint8'     => 'Salve "Seu Client ID" e "Seu Client Secret" para depois configurar na extensão no plesk',
    'supportMe'      => "Para me ajudar a manter essa extensão, você pode fazer uma doação de qualquer valor"
];