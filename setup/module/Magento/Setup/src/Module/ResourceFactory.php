<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\Resource;
use Magento\Setup\Module\Setup\ResourceConfig;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResourceFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @return Resource
     */
    public function create(\Magento\Framework\App\DeploymentConfig $deploymentConfig)
    {
        $connectionFactory = $this->serviceLocator->get('Magento\Setup\Module\ConnectionFactory');
        $resource = new Resource(
            new ResourceConfig(),
            $connectionFactory,
            $deploymentConfig
        );
        return $resource;
    }
}
