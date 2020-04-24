Feature:
    In order to fill in my availabilities
    As a user
    I must be able to log in

    @javascript
    Scenario: As anonymous, I cannot access to the homepage
        When I go to the homepage
        Then I should be on "/login"

    Scenario Outline: As a registered user, I can log in
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 01      |
            | user_login[birthday][month] | 01      |
            | user_login[birthday][year]  | 1990    |
        And I press "Je me connecte"
        Then I should be on "/"
        And I should see "NIVOL : 990001A"
        Examples:
            | login              |
            | john.doe@resop.com |
            | 990001A            |

    Scenario: As an authenticated user, I can log out
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/"
        When I follow "Déconnexion"
        Then I should be on "/login"

    Scenario: As anonymous, when I try to log in with a non-existing account, I'm redirected to the registration page
        Given I am on "/login"
        When I fill in the following:
            | user_login[identifier]      | invalid@resop.com |
            | user_login[birthday][day]   | 01                |
            | user_login[birthday][month] | 01                |
            | user_login[birthday][year]  | 1990              |
        And I press "Je me connecte"
        Then I should be on "/user/new"
        And the "user_emailAddress" field should contain "invalid@resop.com"

    Scenario: As anonymous, I cannot log in with empty data
        Given I am on "/login"
        When I press "Je me connecte"
        Then I should be on "/user/new"
