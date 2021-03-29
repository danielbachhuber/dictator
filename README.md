# Dictator

**This project is no longer actively maintained. You might look at [boxuk/dictator](https://github.com/boxuk/dictator) as an alternative.**

[![Build Status](https://travis-ci.org/danielbachhuber/dictator.png?branch=master)](https://travis-ci.org/danielbachhuber/dictator)

Dictator controls the State of WordPress. Strongly influenced by [Salt](http://www.saltstack.com/), Dictator permits configuration attributes stored in YAML state files to be *imposed* upon WordPress.

Dictator's primary concern is to permit the expression of how WordPress is configured as YAML state files. It understands WordPress in terms of *states*. States are collections of *regions*. Each state file has the state declaration, and any tracked configuration details for each region.

For example, the `site` state includes the `settings`, `users`, and `terms` regions. Running `wp dictator export site site-state.yml` against production data will export the production state into a human-readable state file:

	state: site
	settings:
	  title: Dictator
	  description: Just another WordPress site controlled by Dictator
	  date_format: F j, Y
	  time_format: g:i a
	  active_theme: twentyfourteen

Apply the state file locally with `wp dictator impose site-state.yml` and you've replicated production without having to download the database.

## Installing

Dictator is a series of [WP-CLI](http://wp-cli.org) commands.

Once WP-CLI is installed, Dictator can be [installed](https://github.com/wp-cli/wp-cli/wiki/Community-Packages) via [Composer](https://getcomposer.org/), or WP-CLI's [`--require`](http://wp-cli.org/config/) attribute.

## Using

Dictator comprises these WP-CLI commands:

* `dictator compare <file>` - Compare a given state file to the State of WordPress. Produces a colorized diff if differences, otherwise empty output.
* `dictator export <state> <file> [--regions=<regions>] [--force]` - Export the State of WordPress to a state file.
* `dictator impose <file> [--regions=<regions>]` - Impose a given state file onto WordPress.
* `dictator validate <file>` - Validate the provided state file against each region's schema.

First time user? Try Dictator out by calling `wp dictator export site site-state.yml`, making a change to the state file, and seeing a colorized diff of how it compares with `wp dictator compare site-state.yml`.

## Extending

Even though Dictator is in its infancy, it was written with extensibility in mind. New states can be registered with `Dictator::add_state();`, and can comprise a selection of existing or custom regions. Write a state / region for your plugin / theme to make it easy for your users to export / impose settings.
