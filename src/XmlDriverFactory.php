<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace YuriGobatto\Component\DoctrineMongoODM;

use Doctrine\ODM\MongoDB\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use YuriGobatto\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

/**
 * Class XmlDriverFactory
 *
 * @package YuriGobatto\Component\DoctrineMongoODM
 */
class XmlDriverFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @return XmlDriver|SimplifiedXmlDriver
     * @throws InvalidConfigException for invalid config service values.
     */
    public function __invoke(ContainerInterface $container)
    {
        $options = $this->getDoctrineConfiguration($container, 'driver');

        if (empty($options[XmlDriver::class])) {
            throw new InvalidConfigException(sprintf('Doctrine driver configuration not found.'));
        }

        try {
            if ($options[XmlDriver::class]['simplified']) {
                return new SimplifiedXmlDriver($options[XmlDriver::class]['xml_dir']);
            }

            return new XmlDriver($options[XmlDriver::class]['xml_dir']);
        } catch (\Exception $e) {
            throw new InvalidConfigException($e->getMessage());
        }
    }
}
