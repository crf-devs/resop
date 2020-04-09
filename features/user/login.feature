Feature:
    In order to fill in my availabilities
    As a user
    I must be able to log in

    Scenario: As anonymous, I cannot access to the homepage
        When I go to the homepage
        Then I should be on "/login"

    Scenario: As a registered user, I can log in
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | user1@resop.com |
            | user_login[birthday][day]   | 01              |
            | user_login[birthday][month] | 01              |
            | user_login[birthday][year]  | 1990            |
        And I press "Je me connecte"
        Then I should be on "/"
        And I should see "NIVOL : 990001A"

    Scenario: As an authenticated user, I can log out
        Given I am authenticated as a user
        And I am on "/"
        When I follow "Déconnexion"
        Then I should be on "/login"
