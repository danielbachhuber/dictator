Feature: Site Widgets Region

  Scenario: Impose Site Widgets
    Given a WP install
    And a site-state.yml file:
      """
      state: site
      settings:
        active_theme: p2
      widgets:
        sidebar-1:
          calendar-2:
            title: Calendar
          search-2:
            title: Search Site
        wp_inactive_widgets:
          calendar-3:
            title: Inactive Calendar
      """

    When I run `wp theme install p2 --force`
    Then STDOUT should not be empty

    When I run `wp dictator impose site-state.yml`
    Then STDOUT should not be empty

    When I run `wp dictator compare site-state.yml`
    Then STDOUT should be empty
