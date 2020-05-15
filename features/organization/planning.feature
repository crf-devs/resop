# todo: test javascript behaviors (mark slot as ...)
@planning
Feature:
    In order to know the availability of the resources in my organization,
    As an organization
    I must have access to the planning and I can filter what is displayed.

    Scenario: As an organization, I have access to the planning and I can see my resources
        Given I am authenticated as "UL 01-02"
        And I am on "/organizations/203"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then the response status code should be 200
        And I should be on "/organizations/203/planning/"
        And I should see "VPSP - 75012"
        And I should see "VPSP - 75014"
        And I should see "VL - 75016"
        And I should see "VL - 75018"
        And I should not see "7599"
        And I should not see "7799"
        And I should not see "7710"
        And I should see "Jane DOE"
        And I should not see "John DOE"

    Scenario: As an organization, I have access to the planning and I can see my resources' availability
        Given I am authenticated as "UL 01-02"
        And I am on "/organizations/203"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then the response status code should be 200
        And availability of user "Jane DOE" should be "unknown" on "next monday" at 02:00
        And availability of user "Jane DOE" should be "booked" on "next monday" at 06:00
        And availability of user "Jane DOE" should be "available" on "next monday" at 10:00
        And availability of user "Jane DOE" should be "locked" on "next monday" at 14:00
        And availability of asset "VPSP - 75012" should be "unknown" on "tuesday next week" at 02:00
        And availability of asset "VPSP - 75012" should be "locked" on "tuesday next week" at 06:00
        And availability of asset "VPSP - 75012" should be "available" on "tuesday next week" at 10:00
        And availability of asset "VPSP - 75012" should be "booked" on "tuesday next week" at 14:00

    Scenario: As a parent organization, I have access to the planning of my children organizations
        Given I am authenticated as "DT75"
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

    Scenario: As an organization, I can filter the resources displayed on planing
        Given I am authenticated as "DT75"
        And I am on "/organizations/201/planning/"
        Then I should not see "John DOE"
        And I should see "VPSP - 75992"
        And I should see "VL - 75996 "
        When I select "VPSP" from "assetTypes[]"
        And I check "displayVulnerables"
        And I press "search-planning-button"
        Then the response status code should be 200
        And I should see "John DOE"
        And I should see "VPSP - 75992"
        And I should not see "VL - 75996"
        When I fill in the following:
            | hideUsers  | 1 |
            | hideAssets | 1 |
        And I press "search-planning-button"
        Then the response status code should be 200
        And I should see "Aucune resource ne correspond à votre recherche"
