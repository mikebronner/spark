# Spark

- [Introduction](#introduction)
- [Installation](#installation)
- [Defining Subscription Plans](#defining-subscription-plans)
- [Teams](#teams)
- [Customizing Spark Views](#customizing-spark-views)
- [Customizing Spark JavaScript](#customizing-spark-javascript)

<a name="introduction"></a>
## Introduction

**This is an alpha, experimental release of Spark. Things will change. Things will break. Thank you for testing!**

Spark is an experimental project primarily intended for building business oriented SaaS applications, and is highly opinionated towards that use case.

<a name="installation"></a>
## Installation
### New Project
First, install the Spark installer and make sure that the global Composer `bin` directory is within your system's `$PATH`:
```sh
	composer global require "laravel/spark-installer=~1.0"
```

Next, create a new Laravel application and install Spark:
```sh
	laravel new application

	cd application

	spark install
```

### Existing Project
First, pull in the guzzle and spark packages:
```sh
	composer require guzzlehttp/guzzle:^6.0
	composer require laravel/spark:^0.1.0
```

Then add the service provider to the `providers` array in `\app\config\app.conf` (don't forget to replace `App` with your namespace):
```php
	/**
	 * Spark Service Providers...
	 */
	App\Providers\SparkServiceProvider::class,
```

Lastly, run the spark installer command:
```sh
	php artisan spark:install
```

### Post-Installation Steps
After installing Spark, be sure to migrate your database, install the NPM dependencies, and run the `gulp` command. You should also set the `AUTHY_KEY`, `STRIPE_KEY`, and `STRIPE_SECRET` environment variables in your `.env` file.

You may also wish to review the `SparkServiceProvider` class that was installed in your application. This provider is the central location for customizing your Spark installation.

<a name="defining-subscription-plans"></a>
## Defining Subscription Plans

Subscription plans may be defined in your `app/Providers/SparkServiceProvider.php` file. This file contains a `customizeSubscriptionPlans` method. Within this method, you may define all of your application's subscription plans. There are a few examples in the method to get you started.

When defining a Spark plan, the `plan` method accepts two arguments: the name of the plan and the Stripe ID of the plan. Be sure that the Stripe ID given to the `plan` method corresponds to a plan ID on your Stripe account:
```php
	Spark::plan('Display Name', 'stripe-id')
		->price(10)
		->features([
			//
		]);
```

### Yearly Plans

To define a yearly plan, simply call the `yearly` method on the plan definition:
```php
	Spark::plan('Basic', 'basic-yearly')
		->price(100)
		->yearly()
		->features(
			//
		);
```
### Coupons

To use a coupon, simply create the coupon on Stripe and access the `/register` route with a `coupon` query string variable that matches the ID of the coupon on Stripe.

	http://stripe.app/register?coupon=code

Site-wide promotions may be run using the `Spark::promotion` method within your `SparkServiceProvider`:
```php
	Spark::promotion('coupon-code');
```
<a name="teams"></a>
## Teams

To enable teams, simply use the `CanJoinTeams` trait on your `User` model. The trait has already been imported in the top of the file, so you only need to add it to the model itself:
```php
	class User extends Model implements TwoFactorAuthenticatableContract,
	                                    BillableContract,
	                                    CanResetPasswordContract
	{
	    use Billable, CanJoinTeams, CanResetPassword, TwoFactorAuthenticatable;
	}
```
Once teams are enabled, a team name will be required during registration, and a `Teams` tab will be available in the user settings dashboard.

### Roles

Team roles may be defined in the `customizeRoles` method of the `SparkServiceProvider`.

<a name="customizing-spark-views"></a>
## Customizing Spark Views

You may publish Spark's Blade views by using the `vendor:publish` command:
```
	php artisan vendor:publish --tag=spark-basics
```
The published views will be placed in `resources/views/vendor/spark`.

<a name="customizing-spark-javascript"></a>
## Customizing Spark JavaScript

The `resources/assets/js/spark/components.js` file contains the statements to load some common Spark Vue components. [Vue](http://vuejs.org) is the JavaScript framework used by the Spark registration and settings screens.

You are free to change any of these require statements to load your own Vue component for a given screen. Most likely, you will want to copy the original component as a starting point for your customization.
