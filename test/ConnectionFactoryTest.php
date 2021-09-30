<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace Helderjs\Test\Component\DoctrineMongoODM;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Helderjs\Component\DoctrineMongoODM\ConnectionFactory;
use Helderjs\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use MongoDB\Client;
use Psr\Container\ContainerInterface;

class ConnectionFactoryTest extends TestCase
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
        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(false);
        $connection = $factory($this->container->reveal());
        $this->assertInstanceOf(Client::class, $connection);

        $this->container->has('doctrine')->willReturn(true);
        $this->container->get('doctrine')->willReturn([]);
        $this->container->has('config')->willReturn(false);
        $connection = $factory($this->container->reveal());
        $this->assertInstanceOf(Client::class, $connection);

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $connection = $factory($this->container->reveal());
        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithEmptyDoctrineConfig()
    {
        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => []]);
        $connection = $factory($this->container->reveal());

        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithNoConnectionConfig()
    {
        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => ['connection' => []]]);
        $connection = $factory($this->container->reveal());
        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithMissingDoctrineConfig()
    {
        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'connection' => [
                    'odm_default' => [
                        'port' => '27017',
                    ],
                ],
            ],
        ];

        $factory = new ConnectionFactory();
        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithDoctrineConfigConnectionString()
    {
        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'connection' => [
                    'odm_default' => [
                        'connection_string' => 'mongodb://username:password@localhost:27017/mydb',
                        'options' => [],
                    ],
                ],
            ],
        ];

        $configuration = $this->prophesize(Configuration::class);
        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $connection = $factory($this->container->reveal());

        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithDoctrineConfigParams()
    {
        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'connection' => [
                    'odm_default' => [
                        'server' => 'localhost',
                        'port' => '27017',
                        'user' => 'user',
                        'password' => 'password',
                        'dbname' => 'mydb',
                        'options' => [
                            'journal' => true,
                            'readPreference' => 'secondary',
                        ],
                    ],
                ],
            ],
        ];

        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $connection = $factory($this->container->reveal());

        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithDoctrineWithoutConfigurationClass()
    {
        $options = [
            'doctrine' => [
                'default' => 'odm_default',
                'connection' => [
                    'odm_default' => [
                        'server' => 'localhost',
                        'port' => '27017',
                        'user' => 'user',
                        'password' => 'password',
                        'dbname' => 'mydb',
                        'options' => [
                            'journal' => true,
                            'readPreference' => 'secondary',
                        ],
                    ],
                ],
            ],
        ];

        $factory = new ConnectionFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->container->has(Configuration::class)->willReturn(false);
        $connection = $factory($this->container->reveal());

        $this->assertInstanceOf(Client::class, $connection);
    }

    public function testCallingFactoryWithTwoDoctrineConfig()
    {
        $options1 = [
            'doctrine' => [
                'default' => 'odm_default',
                'connection' => [
                    'odm_default' => [
                        'connection_string' => 'mongodb://username:password@localhost:27017/mydb',
                        'options' => [],
                    ],
                ],
            ],
        ];

        $options2 = [
            'doctrine' => [
                'default' => 'odm_secondary',
                'connection' => [
                    'odm_default' => [
                        'connection_string' => 'mongodb://user1:hardPassword@myserver:27017/mydb2',
                        'options' => [],
                    ],
                ],
            ],
        ];

        $factory1 = new ConnectionFactory();
        $factory2 = new ConnectionFactory('odm_secondary');

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options1);
        /**
         * @var Client $connection1
         */
        $connection1 = $factory1($this->container->reveal());

        $this->container->has('doctrine')->willReturn(false);
        $this->container->get('config')->willReturn($options2);
        $this->container->has('config')->willReturn(false);
        /**
         * @var Client $connection2
         */
        $connection2 = $factory2($this->container->reveal());

        $this->assertInstanceOf(Client::class, $connection1);
        $this->assertInstanceOf(Client::class, $connection2);
        $this->assertNotSame($connection1, $connection2);
    }
}
