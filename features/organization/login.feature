Feature:
    In order to manage my actions
    As an organization
    I must be able to log in

    Scenario: As anonymous, I cannot access to the homepage
        When I go to "/organizations"
        Then I should be on "/organizations/login"

    Scenario Outline: As a registered organization, I can log in
        Given I am on "/organizations/login"
        When I select "<identifier>" from "identifier"
        And I fill in "password" with "covid19"
        And I press "Je me connecte"
        Then I should be on "/organizations/"
        And I should see "<name>"
        Examples:
            | identifier | name            |
            | DT75       | DT75            |
            | UL 01-02   | DT75 - UL 01-02 |

    Scenario: As an authenticated organization, I can log out
        Given I am authenticated as "UL 01-02"
        And I am on "/organizations/"
        When I follow "Déconnexion"
        Then I should be on "/organizations/login"
