Feature:
    In order to find a user or an asset
    As an organization
    I must be able to search users and assets

    Scenario: As anonymous, I cannot access to the search page
        When I go to "/organizations/search?query=foo"
        Then I should be on "/organizations/login"

    Scenario: As an authenticated organization, I can search for a user
        Given I am authenticated as an organization
        And I am on "/organizations/"
        When I fill in "query" with " UsER1 reSOp "
        And I press "Rechercher"
        Then I should be on "/organizations/search"
        And I should see "Rechercher \"UsER1 reSOp\""
        And I should see "990001A"
        And I should see "user1@resop.com"
        And I should see "Aucun véhicule ne correspond à votre recherche."

    Scenario: As an authenticated organization, I can search for an asset
        Given I am authenticated as an organization
        And I am on "/organizations/"
        When I fill in "query" with " 75052 "
        And I press "Rechercher"
        Then I should be on "/organizations/search"
        And I should see "Rechercher \"75052\""
        And I should see "VPSP - 75052"
        And I should see "Aucun bénévole ne correspond à votre recherche."
