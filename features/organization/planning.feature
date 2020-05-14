# todo: test javascript behaviors (mark slot as ...)
@planning
Feature:
    In order to know the availability of the resources in my organization,
    As an organization
    I must have access to the planning and I can filter what is displayed.

    Scenario: As an organization, I have access to the planning and I can see my resources
        Given I am authenticated as "admin204@resop.com"
        And I am on "/organizations/204"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then the response status code should be 200
        And I should be on "/organizations/204/planning/"
        And I should see "VPSP - 77102"
        And I should see "VPSP - 77104"
        And I should see "VL - 77106"
        And I should see "VL - 77108"
        And I should not see "7599"
        And I should not see "7510"
        And I should see "Chuck NORRIS"
        And I should see "Freddy MERCURY"
        And I should not see "John DOE"
        And I should not see "Jane DOE"

    Scenario: As an organization, I have access to the planning and I can see my resources' availability
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then I should be on "/organizations/201/planning/"
        And the response status code should be 200
        And availability of user "John DOE" should be "locked" on "next monday" at 02:00
        And availability of user "John DOE" should be "unknown" on "next monday" at 10:00
        And availability of user "John DOE" should be "available" on "next tuesday" at 10:00
        And availability of asset "VPSP - 75992" should be "unknown" on "tuesday next week" at 02:00

    Scenario: As a parent organization, I have access to the planning of my children organizations
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then the response status code should be 200
        And I should be on "/organizations/201/planning/"
        And I should not see "Jane DOE"
        When I fill in the following:
            | organizations[] | 201 |
            | organizations[] | 203 |
        And I press "search-planning-button"
        Then the response status code should be 200
        And I should be on "/organizations/201/planning/"
        And I should see "Jane DOE"

    Scenario: As an organization, I can filter the resources displayed on planning
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201/planning/"
        Then I should see "Jane DOE"
        And I should see "John DOE"
        And I should see "VPSP - 75992"
        And I should see "VL - 75996"
        When I select "VPSP" from "assetTypes[]"
        And I select "1" from "userPropertyFilters[vulnerable]"
        And I press "search-planning-button"
        Then I should be on "/organizations/201/planning/"
        And I should see "John DOE"
        And I should not see "Jane DOE"
        And I should see "VPSP - 75992"
        And I should not see "VL - 75996"
        And I select "0" from "userPropertyFilters[vulnerable]"
        And I press "search-planning-button"
        Then I should be on "/organizations/201/planning/"
        And I should not see "John DOE"
        And I should see "Jane DOE"
        When I check "hideUsers"
        And I check "hideAssets"
        And I press "search-planning-button"
        Then I should be on "/organizations/201/planning/"
        And I should see "Aucune resource ne correspond à votre recherche"
