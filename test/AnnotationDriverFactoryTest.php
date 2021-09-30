<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace YuriGobatto\Test\Component\DoctrineMongoODM;

use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use YuriGobatto\Component\DoctrineMongoODM\AnnotationDriverFactory;
use YuriGobatto\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

class AnnotationDriverFactoryTest extends TestCase
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
        $factory = new AnnotationDriverFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());


        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());

        $this->container->has('doctrine')->willReturn(true);
        $this->container->get('doctrine')->willReturn([]);
        $this->container->has('config')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithEmptyDoctrineConfig()
    {
        $factory = new AnnotationDriverFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => []]);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithEmptyDriverDoctrineConfig()
    {
        $factory = new AnnotationDriverFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['doctrine' => ['driver' => []]]);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithWrongDriverDoctrineConfig()
    {
        $options = [
            'doctrine' => [
                'driver' => [
                    'odm_default' => [
                        AnnotationDriver::class => [],
                    ],
                ],
            ],
        ];
        $factory = new AnnotationDriverFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCallingFactoryWithDriverDoctrineConfig()
    {
        $options = [
            'doctrine' => [
                'driver' => [
                    'odm_default' => [
                        AnnotationDriver::class => [
                            'documents_dir' => ['./src/myApp/Documents'],
                        ],
                    ],
                ],
            ],
        ];
        $factory = new AnnotationDriverFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);

        $driver = $factory($this->container->reveal());
        $this->assertInstanceOf(AnnotationDriver::class, $driver);
    }
}
