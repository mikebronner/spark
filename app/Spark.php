<?php

namespace Laravel\Spark;

use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Services\Auth\TwoFactor\Authy;
use Laravel\Spark\Ux\Settings\Tabs as SettingsTabs;

class Spark
{
    /**
     * The Spark plan collection instance.
     *
     * @var \Laravel\Spark\Subscriptions\Plans
     */
    protected static $plans;

    /**
     * The coupon-code that is being forced as promo.
     *
     * @var string
     */
    protected static $forcedPromo;

    /**
     * The callback used to retrieve the users.
     *
     * @var callable|null
     */
    public static $retrieveUsersWith;

    /**
     * The callback used to retrieve the user registration validator.
     *
     * @var callable|null
     */
    public static $validateRegistrationsWith;

    /**
     * The callback used to create the new users.
     *
     * @var callable|null
     */
    public static $createUsersWith;

    /**
     * Indicates if two-factor authentication is supported.
     *
     * @var bool
     */
    public static $twoFactorAuth = false;

    /**
     * The callback used to retrieve the user profile validator.
     *
     * @var callable|null
     */
    public static $validateProfileUpdatesWith;

    /**
     * The callback used to update the user's profiles.
     *
     * @var callable|null
     */
    public static $updateProfilesWith;

    /**
     * The invoice's meta attributes.
     *
     * @var array
     */
    public static $invoiceData = [];

    /**
     * The settings tabs configuration.
     *
     * @var \Laravel\Spark\Ux\Settings\Tabs
     */
    public static $settingsTabs;

    /**
     * The Spark configuration options.
     *
     * @var array
     */
    protected static $options = [];

    /**
     * Configure the Spark application.
     *
     * @param  array  $options
     * @return void
     */
    public static function configure(array $options)
    {
        static::$options = $options;
    }

    /**
     * Get a Spark configuration option.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function option($key, $default)
    {
        return array_get(static::$options, $key, $default);
    }

    /**
     * Define a new Spark plan.
     *
     * @param  string  $name
     * @param  string  $id
     * @return \Laravel\Spark\Subscriptions\Plan
     */
    public static function plan($name, $id = null)
    {
        return static::plans()->create($name, $id);
    }

    /**
     * Get the Spark plan collection.
     *
     * @return \Laravel\Spark\Subscriptions\Plans
     */
    public static function plans()
    {
        return static::$plans ?: static::$plans = new Subscriptions\Plans;
    }

    /**
     * Set a forced coupon-code as a promo.
     *
     * @param  string  $couponCode
     * @return void
     */
    public static function promotion($couponCode)
    {
        static::$forcedPromo = $couponCode;
    }

    /**
     * Get the coupon-code that is being forced as a promo.
     *
     * @return string
     */
    public static function forcedPromo()
    {
        return static::$forcedPromo;
    }

    /**
     * Determine if a coupon-code is currently being forced.
     *
     * @return bool
     */
    public static function forcingPromo()
    {
        return isset(static::$forcedPromo);
    }

    /**
     * Retrieve the current user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public static function user()
    {
        return static::$retrieveUsersWith
                        ? call_user_func(static::$retrieveUsersWith)
                        : Auth::user();
    }

    /**
     * Set a callback to be used to retrieve the user.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function retrieveUsersWith(callable $callback)
    {
        static::$retrieveUsersWith = $callback;
    }

    /**
     * Set a callback to be used to retrieve the user validator.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function validateRegistrationsWith(callable $callback)
    {
        static::$validateRegistrationsWith = $callback;
    }

    /**
     * Set a callback to be used to create the users.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function createUsersWith(callable $callback)
    {
        static::$createUsersWith = $callback;
    }

    /**
     * Specify that two-factor authentication should be available.
     *
     * @return void
     */
    public static function withTwoFactorAuth()
    {
        static::$twoFactorAuth = true;
    }

    /**
     * Determine if the application supports two-factor authentication.
     *
     * @return bool
     */
    public static function supportsTwoFactorAuth()
    {
        return static::$twoFactorAuth;
    }

    /**
     * Get the default two-factor authentication provider.
     *
     * Currently Authy is the only provider, so this is not configurable.
     *
     * @return  \Laravel\Spark\Contracts\Auth\TwoFactor\Provider
     */
    public static function twoFactorProvider()
    {
        return new Authy;
    }

    /**
     * Set a callback to be used to retrieve the user profile validator.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function validateProfileUpdatesWith(callable $callback)
    {
        static::$validateProfileUpdatesWith = $callback;
    }

    /**
     * Set a callback to be used to update the user's profiles.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function updateProfilesWith(callable $callback)
    {
        static::$updateProfilesWith = $callback;
    }

    /**
     * Get or set the Cashier invoice's meta attributes.
     *
     * @param  array  $invoiceData
     * @return array|null
     */
    public static function generateInvoicesWith(array $invoiceData = null)
    {
        if (is_null($invoiceData)) {
            return static::$invoiceData;
        } else {
            static::$invoiceData = $invoiceData;
        }
    }

    /**
     * Get the configuration for the Spark settings tabs.
     *
     * @return \Laravel\Spark\Ux\Settings\Tabs
     */
    public static function settingsTabs()
    {
        return static::$settingsTabs ?: static::$settingsTabs = static::createDefaultSettingsTabs();
    }

    /**
     * Create the default settings tabs configuration.
     *
     * @return \Laravel\Spark\Ux\Settings\Tabs
     */
    protected static function createDefaultSettingsTabs()
    {
        $tabs = [(new SettingsTabs)->profile(), (new SettingsTabs)->security()];

        if (count(static::plans()->active()) > 0) {
            $tabs[] = (new SettingsTabs)->subscription();
        }

        return new SettingsTabs($tabs);
    }

    /**
     * Get the key for the first settings tab in teh collection.
     *
     * @return string
     */
    public static function firstSettingsTabKey()
    {
        return static::settingsTabs()->tabs[0]->key;
    }

    /**
     * Get the full path to a Spark resource.
     *
     * @param  string  $path
     * @return string
     */
    public static function resource($path)
    {
        $paths = [
            base_path().'/resources/assets/vendor/spark',
            SPARK_PATH.'/resources/assets/'
        ];

        foreach ($paths as $basePath) {
            if (file_exists($basePath.$path)) {
                return $basePath.$path;
            }
        }
    }
}