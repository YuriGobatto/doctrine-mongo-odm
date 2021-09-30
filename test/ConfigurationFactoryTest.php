<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace YuriGobatto\Test\Component\DoctrineMongoODM;

use Doctrine\Common\Cache\Cache;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use YuriGobatto\Component\DoctrineMongoODM\ConfigurationFactory;
use YuriGobatto\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use Helders\Test\Component\DoctrineMongoODM\Asset\MyFilter;
use Helders\Test\Component\DoctrineMongoODM\Asset\MyType;
use Psr\Container\ContainerInterface;

class ConfigurationFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCallingFactoryWithNoConfigReturns()
    {
        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(false);
        $config = $factory($this->container->reveal());
        $this->assertInstanceOf(Configuration::class, $config);

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $config = $factory($this->container->reveal());
        $this->assertInstanceOf(Configuration::class, $config);

        $this->container->has('doctrine')->willReturn(true);
        $this->container->get('doctrine')->willReturn([]);
        $this->container->has('config')->willReturn(true);
        $config = $factory($this->container->reveal());
        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testCallingFactoryWithEmptyDoctrineConfig()
    {
        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => []]);
        $config = $factory($this->container->reveal());

        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testCallingFactoryWithWrongDoctrineConfig()
    {
        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => ['configuration' => []]]);
        $config = $factory($this->container->reveal());

        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testCallingFactoryWithMissingDoctrineConfig()
    {
        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'configuration' => [
                    'odm_default' => [
                        'metadata_cache'     => 'array',
                        'generate_proxies'   => true,
                        'proxy_dir'          => 'data/DoctrineMongoODMModule/Proxy',
                        'proxy_namespace'    => 'DoctrineMongoODMModule\Proxy',
                        'generate_hydrators' => true,
                        'hydrator_dir'       => 'data/DoctrineMongoODMModule/Hydrator',
                        'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
                    ],
                ],
            ],
        ];

        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithMinimumDoctrineConfig()
    {
        $metadataCache = $this->prophesize(Cache::class);
        $mappingDriver = $this->prophesize(MappingDriver::class);

        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'configuration' => [
                    'odm_default' => [
                        'default_db' => 'mydb',
                        'driver' => MappingDriver::class,
                        'generate_proxies' => 2,
                        'proxy_dir' => 'data/DoctrineMongoODMModule/Proxy',
                        'proxy_namespace' => 'DoctrineMongoODMModule\Proxy',
                        'generate_hydrators' => true,
                        'hydrator_dir' => 'data/DoctrineMongoODMModule/Hydrator',
                        'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
                    ],
                ],
            ],
        ];

        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->container->get(Cache::class)->willReturn($metadataCache->reveal());
        $this->container->get(MappingDriver::class)->willReturn($mappingDriver->reveal());
        $config = $factory($this->container->reveal());

        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testCallingFactoryWithDoctrineConfigComplete()
    {
        $metadataCache = $this->prophesize(Cache::class);
        $mappingDriver = $this->prophesize(MappingDriver::class);

        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'configuration' => [
                    'odm_default' => [
                        'default_db' => 'mydb',
                        'driver' => MappingDriver::class,
                        'metadata_cache' => Cache::class,
                        'generate_proxies' => 2,
                        'proxy_dir' => 'data/DoctrineMongoODMModule/Proxy',
                        'proxy_namespace' => 'DoctrineMongoODMModule\Proxy',
                        'generate_hydrators' => true,
                        'hydrator_dir' => 'data/DoctrineMongoODMModule/Hydrator',
                        'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
                        'filters' => [
                            'myFilter' => MyFilter::class,
                        ],
                        'types' => [
                            'myType' => MyType::class,
                            Type::CUSTOMID => MyType::class,
                        ],
                        'metadata_factory_name' => 'stdClass',
                        'logger' => function (array $log) {
                            print_r($log);
                        }
                    ],
                ],
            ],
        ];

        $factory = new ConfigurationFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->container->get(Cache::class)->willReturn($metadataCache->reveal());
        $this->container->get(MappingDriver::class)->willReturn($mappingDriver->reveal());
        $config = $factory($this->container->reveal());

        $this->assertInstanceOf(Configuration::class, $config);
    }
}
