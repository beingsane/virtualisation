<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class NginxTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration(new ServerConfig(__DIR__ . '/../fixtures/j3x.xml'));
		$this->service = $serverFactory->getWebserver();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'nginx-1.9' => [
				'image'   => 'nginx:1.9',
				'volumes' => [
					getcwd() . '/vendor:/usr/local/lib/php/vendor',
					'dockyard/docker/nginx-1.9/conf:/etc/nginx/conf.d',
					'dockyard/docker/nginx-1.9/html/j3-postgresql.dev:/var/www/html/j3-postgresql.dev',
				],
				'links'   => [
					'php-latest',
				],
			],
		];

		$result = $this->service->getSetup();

		$this->assertEquals($expected, $result);
	}

	public function testFilesystemIsSetUp()
	{
		$this->service->prepare();

		$this->assertFileContains(
			'dockyard/docker/nginx-1.9/conf/j3-postgresql.dev/vhost.conf',
			[
				'server_name   j3-postgresql.dev;',
				'root          /var/www/html/j3-postgresql.dev;',
			]
		);
	}
}
