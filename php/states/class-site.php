<?php

namespace Dictator\States;

class Site extends State {

	protected $regions = array(
		'settings'   => '\Dictator\Regions\Site_Settings',
		'users'      => '\Dictator\Regions\Site_Users',
		'widgets'    => '\Dictator\Regions\Site_Widgets',
		'terms'      => '\Dictator\Regions\Terms',
		);

}