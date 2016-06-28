<?php

namespace teliasorg\autocompleteAjax;

use \yii\web\AssetBundle;

/**
 * @author KeyGen <keygenqt@gmail.com>
 * @author Thiago Elias <thiago@thiagoelias.org>
 */
class ActiveAssets extends AssetBundle
{
	public $sourcePath = '@teliasorg/autocompleteAjax/assets';

	public $js = [
		'js/jquery-ui-1.9.2.custom.min.js',
	];

	public $depends = [
		'yii\web\JqueryAsset'
	];

	public $css = [
		'css/jquery-ui-1.9.2.custom.min.css',
		'css/yii2-autocomplete-ajax.css',
	];
}
