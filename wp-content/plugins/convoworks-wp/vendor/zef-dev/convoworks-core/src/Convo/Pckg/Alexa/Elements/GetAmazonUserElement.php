<?php

declare (strict_types=1);
namespace Convo\Pckg\Alexa\Elements;

use Convo\Core\Adapters\Alexa\Api\AmazonUserApi;
use Convo\Core\DataItemNotFoundException;
use Convo\Core\Publish\IPlatformPublisher;
use Convo\Core\Rest\RestSystemUser;
use Convo\Core\Workflow\AbstractWorkflowComponent;
use Convo\Core\Workflow\IConversationElement;
class GetAmazonUserElement extends AbstractWorkflowComponent implements IConversationElement
{
    private $_name;
    /**
     * @var AmazonUserApi
     */
    private $_amazonUserApi;
    /**
     * @var \Convo\Core\IServiceDataProvider
     */
    private $_convoServiceDataProvider;
    public function __construct($properties, $amazonUserApi, $convoServiceDataProvider)
    {
        parent::__construct($properties);
        $this->_name = $properties['name'] ?? 'user';
        $this->_amazonUserApi = $amazonUserApi;
        $this->_convoServiceDataProvider = $convoServiceDataProvider;
    }
    public function read(\Convo\Core\Workflow\IConvoRequest $request, \Convo\Core\Workflow\IConvoResponse $response)
    {
        $scope_type = \Convo\Core\Params\IServiceParamsScope::SCOPE_TYPE_SESSION;
        $params = $this->getService()->getServiceParams($scope_type);
        $service_id = $this->getService()->getId();
        $amazon_config = $this->_convoServiceDataProvider->getServicePlatformConfig(new RestSystemUser(), $service_id, IPlatformPublisher::MAPPING_TYPE_DEVELOP)['amazon'] ?? [];
        if (\is_a($request, '\\Convo\\Core\\Adapters\\Alexa\\AmazonCommandRequest') && !empty($amazon_config)) {
            $token = $request->getAccessToken();
            $this->_logger->debug("Got token from request [{$token}] and service id from service [{$service_id}]");
            $accountLinkingMode = $amazon_config['account_linking_mode'] ?? '';
            if ($accountLinkingMode === 'amazon') {
                if (!$token) {
                    throw new DataItemNotFoundException("Missing token from request.");
                }
                $user = $this->_amazonUserApi->getAmazonUserFromAlexa($request);
                $this->_logger->debug('Got Amazon User with token [' . $request->getAccessToken() . '][' . \json_encode($user) . ']');
                $params->setServiceParam($this->_name, $user);
            } else {
                $this->_logger->error('Account linking with mode ' . $accountLinkingMode . ' is not supported.');
            }
        }
    }
}
