<?php

namespace Vinelab\Cdn\Providers;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Output\ConsoleOutput;
use Vinelab\Cdn\Contracts\CdnHelperInterface;
use Vinelab\Cdn\Providers\Contracts\ProviderInterface;
use Vinelab\Cdn\Validators\Contracts\ProviderValidatorInterface;

/**
 * Class CloudFlareProvider
 * CloudFlare CDN
 *
 *
 * @category Driver
 *
 * @property string  $provider_url
 *
 */
class CloudFlareProvider extends Provider implements ProviderInterface
{
    /**
     * All the configurations needed by this class with the
     * optional configurations default values.
     *
     * @var array
     */
    protected $default = [
        'url' => null,
    ];

    /**
    * Required configurations (must exist in the config file).
    *
    * @var array
    */
    protected $rules = ['url'];

    /**
     * this array holds the parsed configuration to be used across the class.
     *
     * @var Array
     */
    protected $supplier;

    /**
     * @var Instance of Guzzle\Batch\BatchBuilder
     */
    protected $batch;

    /**
     * @var \Vinelab\Cdn\Contracts\CdnHelperInterface
     */
    protected $cdn_helper;

    /**
     * @var \Vinelab\Cdn\Validators\Contracts\ConfigurationsInterface
     */
    protected $configurations;

    /**
     * @var \Vinelab\Cdn\Validators\Contracts\ProviderValidatorInterface
     */
    protected $provider_validator;

    /**
     * @param \Symfony\Component\Console\Output\ConsoleOutput              $console
     * @param \Vinelab\Cdn\Validators\Contracts\ProviderValidatorInterface $provider_validator
     * @param \Vinelab\Cdn\Contracts\CdnHelperInterface                    $cdn_helper
     */
    public function __construct(
        ConsoleOutput $console,
        ProviderValidatorInterface $provider_validator,
        CdnHelperInterface $cdn_helper
    ) {
        $this->console = $console;
        $this->provider_validator = $provider_validator;
        $this->cdn_helper = $cdn_helper;
    }

    /**
     * Read the configuration and prepare an array with the relevant configurations
     * for the (Cloudflare) provider. and return itself.
     *
     * @param $configurations
     *
     * @return $this
     */
    public function init($configurations)
    {
        // merge the received config array with the default configurations array to
        // fill missed keys with null or default values.
        $this->default = array_replace_recursive($this->default, $configurations);

        $supplier = [
            'provider_url' => $this->default['url'],
        ];

        // check if any required configuration is missed
        $this->provider_validator->validate($supplier, $this->rules);

        $this->supplier = $supplier;

        return $this;
    }

    /**
     * This function will be called from the CdnFacade class when
     * someone use this {{ Cdn::asset('') }} facade helper.
     *
     * @param $path
     *
     * @return string
     */
    public function urlGenerator($path)
    {
        $url = $this->cdn_helper->parseUrl($this->getUrl());
        $cloudflarePath = str_replace('public/', '', $path);
        return $url['scheme'].'://'.$url['host'].'/'.$cloudflarePath;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return rtrim($this->provider_url, '/').'/';
    }

    /**
     * @param $attr
     *
     * @return Mix | null
     */
    public function __get($attr)
    {
        return isset($this->supplier[$attr]) ? $this->supplier[$attr] : null;
    }


    public function upload($assets) {}

    public function emptyBucket() {}

    public function getCloudFront() {}

    public function getCloudFrontUrl() {}

    public function getBucket() {}

    public function setS3Client($s3_client) {}

}
