<?php namespace Illuminate\Encryption;

use Illuminate\Support\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('encrypter', function($app)
		{
		    $key    = (string) $app['config']['app.key'];
		    $cipher = $app['config']->has('app.cipher') ? (string) $app['config']['app.cipher'] : null;
			return new Encrypter($key, $cipher);
		});
	}

}
