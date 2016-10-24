Feature: Components of the WP-CLI commands

  Scenario: List available states
    Given a WP install

    When I run `wp dictator list-states`
    Then STDOUT should be a table containing rows:
      | state     | regions                      |
      | network   | settings,users,sites         |
      | site      | settings,users,widgets,terms |
