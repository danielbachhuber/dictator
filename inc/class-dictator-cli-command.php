<?php
/**
 * A WP-CLI command for the Dictator to use.
 */

WP_CLI::add_command( 'dictator', 'Dictator_CLI_Command' );
class Dictator_CLI_Command extends WP_CLI_Command {

	private $state;
	private $regions = array(
			'title',
			'description',
			'theme_mods',
		);

	private $option_key_map = array(
			'title'         => 'blogname',
			'description'   => 'blogdescription',
		);

	/**
	 * Steal this WordPress site's state.
	 * 
	 * @subcommand export-state
	 * @synopsis <state-file> [--region=<region>]
	 */
	public function export_state( $args ) {

		list( $file ) = $args;

		$this->read_state( $file );

		if ( isset( $assoc_args['region'] ) ) {
			$regions = explode( ',', $assoc_args['region'] );
			foreach( $regions as $region ) {
				$this->steal_region( $region );
			}
		} else {
			$this->steal_all_regions();
		}

		$this->save_state( $file );
	}

	/**
	 * Impose a state.yml on this site.
	 * 
	 * @subcommand impose-state
	 * @synopsis <state-file> [--region=<region>]
	 */
	public function impose_state( $args ) {

		list( $file ) = $args;

		$this->read_state( $file );

		if ( isset( $assoc_args['region'] ) ) {
			$regions = explode( ',', $assoc_args['region'] );
			foreach( $regions as $region ) {
				$this->impose_rules_on_region( $region );
			}
		} else {
			$this->impose_all_rules();
		}

		WP_CLI::success( "The dictator's will has been imposed." );

	}

	/**
	 * Read a state.yml file
	 */
	private function read_state( $file ) {

		if ( file_exists( $file ) ) {

			WP_CLI::line( sprintf( "Read existing state: %s", $file ) );
			$contents = file_get_contents( $file );
			$this->state = spyc_load( $contents );
			if ( ! $this->state )
				$this->state = array();
		} else {

			WP_CLI::line( sprintf( "No existing state found: %s", $file ) );
			$this->state = array();
		}
	}

	/**
	 * Save a state.yml file
	 */
	private function save_state( $file ) {

		$ret = file_put_contents( $file, Spyc::YAMLDump( $this->state, false, false, true ) );

		if ( false !== $ret )
			WP_CLI::success( sprintf( "Saved state to file: %s", $file ) );
		else
			WP_CLI::error( sprintf( "Couldn't save state to file: %s", $file ) );
	}

	/**
	 * Steal all of the regions
	 */
	private function steal_all_regions() {

		$regions = $this->regions;
		if ( false !== ( $config_regions = $this->get_config_regions() ) )
			$regions = array_intersect( $regions, $config_regions );

		foreach( $regions as $region ) {
			$this->steal_region( $region );
		}
	}

	/**
	 * Steal the rules for a region
	 * 
	 * @param string $region A named region
	 */
	private function steal_region( $region ) {

		switch ( $region ) {
			case 'title':
			case 'description':
				$this->state[$region] = get_option( $this->option_key_map[$region], '' );
				break;
			case 'theme_mods':
				$current_theme = get_option( 'stylesheet' );
				$this->state[$region] = get_option( 'theme_mods_' . $current_theme );
				break;
		}

		if ( isset( $this->state[$region] ) )
			WP_CLI::line( sprintf( "Stole region rules for the dictator: %s", $region ) );
	}

	/**
	 * Impose all of the rules on behalf of the state
	 */
	private function impose_all_rules() {
		foreach( $this->regions as $region ) {
			$this->impose_rules_on_region( $region );
		}
	}

	/**
	 * Impose rules on a given region
	 */
	private function impose_rules_on_region( $region ) {

		if ( ! isset( $this->state[$region] ) ) {
			WP_CLI::line( sprintf( "No rules imposed on region: %s", $region ) );
			return;
		}

		switch ( $region ) {
			case 'title':
			case 'description':
				update_option( $this->option_key_map[$region], $this->state[$region] );
				break;
			case 'theme_mods':
				$current_theme = get_option( 'stylesheet' );
				update_option( 'theme_mods_' . $current_theme, $this->state[$region] );
				break;	
		}

		WP_CLI::line( sprintf( "Imposed rules on region: %s", $region ) );
	}

	/**
	 * Get the declared regions to dictate from wp-cli.yml
	 * wp-cli.yml or wp-cli.local.yml can serve as a template
	 * for the regions we want to dictate
	 */
	private function get_config_regions() {

		$dictator_configurator = new WP_CLI\Configurator( dirname( __FILE__ ) . '/wp-cli-config-spec.php' );

		list( $args, $assoc_args, $runtime_config ) = $dictator_configurator->parse_args(
			array_slice( $GLOBALS['argv'], 1 ) );

		$config_path = WP_CLI::$runner->get_config_path( $runtime_config );
		$local_config = $dictator_configurator->load_config( $config_path );

		if ( isset( $local_config['dictator']['regions'] ) )
			return $local_config['dictator']['regions'];
		else
			return false;
	}
}