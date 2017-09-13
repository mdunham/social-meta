<?php
namespace Gourmet\SocialMeta\View\Helper;

use Cake\View\Helper;
use Cake\Log\Log;

class UnifiedHelper extends Helper {

	// public $helpers = ['Card', 'OpenGraph'];
	public $helpers = ['Gourmet/SocialMeta.OpenGraph', 'Gourmet/SocialMeta.Card'];

	// public $allTags = array();

	const TAGS_TWITTER = [
		'title', 'image', 'description',
		'card', 'site', 'creator'
	];

	const TAGS_OG = [
		'title', 'image', 'description',
		'type', 'url', 'sitename', 'publishedtime', 'author', 'appid'
	];

	// const TAGS_GOOGLE_PLUS = [
	// 	'description'
	// ];


	public function render() {
		echo $this->OpenGraph->html();
		echo $this->OpenGraph->render();
		echo $this->Card->render();
	}

	public function tag($tagName, $value, $options=null) {
		$all_args = [$value];
		if ($options)
			$all_args[] = $options;
		return $this->__call('set'.$tagName, $all_args);
	}

	public function __call($tag, $args) {
		if (strpos($tag, 'set') !== 0) {
				return parent::__call($tag, $args);
		}

		$newtag = strtolower(substr($tag, 3));

		if (in_array($newtag, self::TAGS_TWITTER)) {
			$args_twitter = $this->filter_options('twitter', $newtag, $args);
			$this->Card->{$tag}(...$args_twitter);
			$tag_found = true;
		}

		if (in_array($newtag, self::TAGS_OG)) {
			$this->OpenGraph->{$tag}(...$args);
			$tag_found = true;
		}

		// if (in_array($newtag, self::TAGS_GOOGLE_PLUS)) {
		// 	$args_gplus = $this->change_namespace('google_plus', $newtag, $args);
		// 	$this->OpenGraph->{$tag}(...$args_gplus);
		// 	$tag_found = true;
		// }

		if (!$tag_found)
			return parent::__call($tag, $args);

		return $this;
	}

	private function filter_options($pOrg, $pTag, $pArgs) {
		if ($pOrg !== 'twitter')
			return [];

		$meta_opts = [];
		if (!empty($pArgs[1]))
			$meta_opts = $pArgs[1];

		$new_opts = null;
		switch($pTag) {
			case 'image':
				$new_opts = remove_options($meta_opts, ['width', 'height']);
				break;

			default:
				$new_opts = $meta_opts;
				break;
		}

		// Form the new argument list
		$result = [];
		foreach ($pArgs as $ind => $tag_arg) {
			if ($ind === 1)
				$result[$ind] = $new_opts;
			else
				$result[$ind] = $tag_arg;
		}
		// Log::write(LOG_INFO, 'RRR '.print_r($result, true));

		return $result;
	}

	// private function change_namespace($pOrg, $pTag, $pArgs) {
	// 	if ($pOrg !== 'google_plus')
	// 		return;
	//
	// 	// Check the last index only if 2 params in arg list.
	// 	$last_ind = -1;
	// 	if (count($pArgs) >= 2)
	// 		$last_ind = count($pArgs) - 1;
	//
	// 	// Form the new argument list - Change namespace
	// 	$result = [];
	// 	foreach ($pArgs as $ind => $tag_arg) {
	// 		if ($ind === $last_ind)
	// 			$result[$ind] = 'ns_none';
	// 		else
	// 			$result[$ind] = $tag_arg;
	// 	}
	//
	// 	// If only "value" is supplied, then add the namespace to the
	// 	// end of arg list.
	// 	if (count($pArgs) === 1)
	// 		$result[] = 'ns_none';
	//
	// 	// Log::write(LOG_INFO, 'RRR '.print_r($result, true));
	//
	// 	return $result;
	// }

}

function remove_options($pOpts, $pKeysToRemove) {
	return array_filter($pOpts, function ($pOpt) use ($pKeysToRemove) {
		$opt_small = mb_strtolower($pOpt);
		return !in_array($opt_small, $pKeysToRemove);
	}, ARRAY_FILTER_USE_KEY);
}
