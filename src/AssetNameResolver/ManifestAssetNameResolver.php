<?php

declare(strict_types = 1);

namespace Oops\WebpackNetteAdapter\AssetNameResolver;

use Oops\WebpackNetteAdapter\Manifest\CannotLoadManifestException;
use Oops\WebpackNetteAdapter\Manifest\ManifestLoader;


final class ManifestAssetNameResolver implements AssetNameResolverInterface
{

	/**
	 * @var string
	 */
	private $manifestName;

	/**
	 * @var ManifestLoader
	 */
	private $loader;

	/**
	 * @var AssetNameResolverInterface|NULL
	 */
	private $fallbackResolver;

	/**
	 * @var string[]|NULL|FALSE
	 */
	private $manifestCache;


	public function __construct(string $manifestName, ManifestLoader $loader, AssetNameResolverInterface $fallbackResolver = null)
	{
		$this->manifestName = $manifestName;
		$this->loader = $loader;
		$this->fallbackResolver = $fallbackResolver;
	}


	public function resolveAssetName(string $asset): string
	{
		if ($this->manifestCache === NULL) {
			try {
				$this->manifestCache = $this->loader->loadManifest($this->manifestName);

			} catch (CannotLoadManifestException $e) {
				if ($this->fallbackResolver === NULL) {
					throw new CannotResolveAssetNameException('Failed to load manifest file.', 0, $e);
				}

				$this->manifestCache = FALSE;
			}
		}

		if ($this->manifestCache === FALSE) {
			return $this->fallbackResolver->resolveAssetName($asset);
		}

		if ( ! isset($this->manifestCache[$asset]) ) {
			throw new CannotResolveAssetNameException(sprintf(
				"Asset '%s' was not found in the manifest file '%s'",
				$asset, $this->loader->getManifestPath($this->manifestName)
			));
		}

		return $this->manifestCache[$asset];
	}

}
