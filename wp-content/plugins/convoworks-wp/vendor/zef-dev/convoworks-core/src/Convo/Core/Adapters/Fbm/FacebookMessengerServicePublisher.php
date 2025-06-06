<?php

declare (strict_types=1);
namespace Convo\Core\Adapters\Fbm;

use Convo\Core\Publish\IPlatformPublisher;
use Convo\Core\Publish\PlatformPublishingHistory;
use Psr\Http\Client\ClientExceptionInterface;
class FacebookMessengerServicePublisher extends \Convo\Core\Publish\AbstractServicePublisher
{
    /**
     * @var \Convo\Core\Adapters\Fbm\FacebookMessengerApiFactory
     */
    private $_facebookMessengerApiFactory;
    /**
     * @var PlatformPublishingHistory
     */
    private $_platformPublishingHistory;
    public function __construct($logger, \Convo\Core\IAdminUser $user, $serviceId, $facebookMessengerApiFactory, $serviceDataProvider, $serviceReleaseManager, $platformPublishingHistory)
    {
        parent::__construct($logger, $user, $serviceId, $serviceDataProvider, $serviceReleaseManager);
        $this->_facebookMessengerApiFactory = $facebookMessengerApiFactory;
        $this->_platformPublishingHistory = $platformPublishingHistory;
    }
    public function getPlatformId()
    {
        return 'facebook_messenger';
    }
    public function export()
    {
        throw new \Exception('Not supported yet');
    }
    public function enable()
    {
        parent::enable();
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        if (isset($config[$this->getPlatformId()])) {
            $this->_updateFacebookMessengerBot();
            $this->_platformPublishingHistory->storePropagationData($this->_serviceId, $this->getPlatformId(), $this->_preparePropagateData());
        } else {
            throw new \Exception("Missing platform config [" . $this->getPlatformId());
        }
    }
    public function propagate()
    {
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        if (isset($config[$this->getPlatformId()])) {
            $this->_updateFacebookMessengerBot();
            $this->_platformPublishingHistory->storePropagationData($this->_serviceId, $this->getPlatformId(), $this->_preparePropagateData());
            $this->_recordPropagation();
        } else {
            throw new \Exception("Missing platform config [" . $this->getPlatformId());
        }
    }
    public function getPropagateInfo()
    {
        $data = parent::getPropagateInfo();
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        if (!isset($config[$this->getPlatformId()])) {
            $this->_logger->debug('No platform [' . $this->getPlatformId() . '] config in service [' . $this->_serviceId . ']. Exiting ... ');
            return $data;
        }
        $this->_logger->info(\print_r($config, \true) . " Accessing platform config");
        $platform_config = $config[$this->getPlatformId()];
        $this->_logger->debug('Got auto mode. Checking further ... ');
        if (isset($platform_config['page_access_token']) && !empty($platform_config['page_access_token'])) {
            $data['allowed'] = \true;
        }
        $meta = $this->_convoServiceDataProvider->getServiceMeta($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        $changesCount = 0;
        if (isset($meta['release_mapping'][$this->getPlatformId()])) {
            $alias = $this->_serviceReleaseManager->getDevelopmentAlias($this->_user, $this->_serviceId, $this->getPlatformId());
            $mapping = $meta['release_mapping'][$this->getPlatformId()][$alias];
            if (!isset($mapping['time_propagated']) || empty($mapping['time_propagated'])) {
                $this->_logger->debug('Never propagated ');
                $data['available'] = \true;
            } else {
                if ($mapping['time_propagated'] < $platform_config['time_updated']) {
                    $this->_logger->debug('Config changed');
                    $configChanged = $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_WEBHOOK_EVENTS, $platform_config['webhook_events']) || $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_WEBHOOK_VERIFY_TOKEN, $platform_config['webhook_verify_token']) || $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_APP_ID, $platform_config['app_id']) || $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_APP_SECRET, $platform_config['app_secret']) || $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_PAGE_ID, $platform_config['page_id']) || $this->_platformPublishingHistory->hasPropertyChangedSinceLastPropagation($this->_serviceId, $this->getPlatformId(), PlatformPublishingHistory::FACEBOOK_MESSENGER_PAGE_ACCESS_TOKEN, $platform_config['page_access_token']);
                    if ($configChanged) {
                        $changesCount++;
                    }
                }
                if (isset($mapping['time_updated']) && $mapping['time_propagated'] < $mapping['time_updated']) {
                    $this->_logger->debug('Mapping changed');
                    $mappingChanged = \true;
                    if ($mappingChanged) {
                        $changesCount++;
                    }
                }
                if ($changesCount > 0) {
                    $data['available'] = \true;
                }
            }
        }
        return $data;
    }
    private function _updateFacebookMessengerBot()
    {
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        $config[$this->getPlatformId()]['webhook_build_status'] = IPlatformPublisher::SERVICE_PROPAGATION_STATUS_IN_PROGRESS;
        $this->_convoServiceDataProvider->updateServicePlatformConfig($this->_user, $this->_serviceId, $config);
        $facebookMessengerApi = $this->_facebookMessengerApiFactory->getApi($this->_user, $this->_serviceId, $this->_convoServiceDataProvider);
        $url = $this->_serviceReleaseManager->getWebhookUrl($this->_user, $this->_serviceId, $this->getPlatformId());
        $facebookMessengerApi->callSubscriptionsApi($url);
        $facebookMessengerApi->callSubscribedApps();
        $facebookMessengerApi->callMessengerProfileApi();
    }
    public function delete(array &$report)
    {
        $facebookMessengerApi = $this->_facebookMessengerApiFactory->getApi($this->_user, $this->_serviceId, $this->_convoServiceDataProvider);
        try {
            $facebookMessengerApi->unsubscribeApp();
            $facebookMessengerApi->callSubscriptionsApiToUnsubscribe();
            $facebookMessengerApi->callMessengerProfileApiToDelete();
            $this->_platformPublishingHistory->removeSoredPropagationData($this->_serviceId, $this->getPlatformId());
            $report['success'][$this->getPlatformId()]['messenger_bot'] = "Messenger bot was successfully unsubscribed from you facebook page.";
        } catch (ClientExceptionInterface $e) {
            $this->_logger->warning($e->getMessage());
            $report['errors'][$this->getPlatformId()]['messenger_bot'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_logger->warning($e->getMessage());
            $report['errors'][$this->getPlatformId()]['messenger_bot'] = $e->getMessage();
        }
    }
    public function getStatus()
    {
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        $status = ['status' => IPlatformPublisher::SERVICE_PROPAGATION_STATUS_FINISHED];
        if (isset($config[$this->getPlatformId()]['webhook_build_status'])) {
            $status['status'] = $config[$this->getPlatformId()]['webhook_build_status'];
        }
        return $status;
    }
    private function _preparePropagateData()
    {
        $config = $this->_convoServiceDataProvider->getServicePlatformConfig($this->_user, $this->_serviceId, IPlatformPublisher::MAPPING_TYPE_DEVELOP);
        return [PlatformPublishingHistory::FACEBOOK_MESSENGER_WEBHOOK_EVENTS => $config[$this->getPlatformId()]['webhook_events'], PlatformPublishingHistory::FACEBOOK_MESSENGER_WEBHOOK_VERIFY_TOKEN => $config[$this->getPlatformId()]['webhook_verify_token'], PlatformPublishingHistory::FACEBOOK_MESSENGER_APP_ID => $config[$this->getPlatformId()]['app_id'], PlatformPublishingHistory::FACEBOOK_MESSENGER_APP_SECRET => $config[$this->getPlatformId()]['app_secret'], PlatformPublishingHistory::FACEBOOK_MESSENGER_PAGE_ID => $config[$this->getPlatformId()]['page_id'], PlatformPublishingHistory::FACEBOOK_MESSENGER_PAGE_ACCESS_TOKEN => $config[$this->getPlatformId()]['page_access_token']];
    }
}
